<?php

namespace APP\plugins\generic\scieloScreening\classes;

use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;

class ScreeningExecutor
{
    private $documentChecker;
    private $orcidClient;

    public function __construct($documentChecker, $orcidClient)
    {
        $this->documentChecker = $documentChecker;
        $this->orcidClient = $orcidClient;
    }

    public function getStatusAuthors($submission)
    {
        $checker = new ScreeningChecker();
        $authors = $submission->getCurrentPublication()->getData('authors');

        $affiliationAuthors = $nameAuthors = $orcidAuthors = [];

        foreach ($authors as $author) {
            $affiliationAuthors[] = $author->getLocalizedAffiliation();
            $nameAuthors[] = $author->getLocalizedGivenName() . " " . $author->getLocalizedFamilyName();
            $orcidAuthors[] = $author->getOrcid();
        }

        list($statusAffiliation, $authorsWithoutAffiliation) = $checker->checkAffiliationAuthors($affiliationAuthors, $nameAuthors);
        return [
            'statusAffiliation' => $statusAffiliation,
            'statusOrcid' => $checker->checkOrcidAuthors($orcidAuthors),
            'authorsWithoutAffiliation' => $authorsWithoutAffiliation
        ];
    }

    private function getStatusMetadataEnglish($submission)
    {
        $publication = $submission->getCurrentPublication();
        $metadataList = array('title', 'abstract', 'keywords');
        $statusMetadataEnglish = true;
        $missingMetadata = [];

        foreach ($metadataList as $metadata) {
            if ($publication->getData($metadata, 'en') == "") {
                $statusMetadataEnglish = false;
                $missingMetadata[] = __("common." . $metadata);
            }
        }

        return [
            'statusMetadataEnglish' => $statusMetadataEnglish,
            'missingMetadataEnglish' => implode(', ', $missingMetadata)
        ];
    }

    private function getStatusPDFs($submission)
    {
        $checker = new ScreeningChecker();
        $galleys = $submission->getGalleys();

        $fileTypeGalleys = array_map(function ($galley) {
            return ($galley->getFileType());
        }, $galleys);

        list($statusPDFs, $numPDFs) = $checker->checkNumberPdfs($fileTypeGalleys);

        return [
            'statusPDFs' => $statusPDFs,
            'numPDFs' => $numPDFs
        ];
    }

    public function getStatusDocumentOrcids($submission)
    {
        if (!$this->documentChecker) {
            return 'Unable';
        }

        $authors = $submission->getCurrentPublication()->getData('authors');
        $documentOrcids = $this->documentChecker->checkTextOrcids();
        if ($authors->count() > count($documentOrcids)) {
            return 'Unable';
        }

        $accessToken = $this->orcidClient->getReadPublicAccessToken();
        foreach ($documentOrcids as $orcid) {
            $orcidWorks = $this->orcidClient->getOrcidWorks($orcid, $accessToken);

            if (!$this->orcidClient->recordHasWorks($orcidWorks)) {
                return 'NotOkay';
            }
        }

        return 'Okay';
    }

    public function getScreeningData($submission)
    {
        $dataScreening = array_merge(
            $this->getStatusAuthors($submission),
            $this->getStatusMetadataEnglish($submission),
            $this->getStatusPDFs($submission),
            ['statusDocumentOrcids' => $this->getStatusDocumentOrcids($submission)]
        );

        return $dataScreening;
    }
}
