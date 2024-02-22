<?php

namespace APP\plugins\generic\scieloScreening\controllers;

use APP\handler\Handler;

use APP\core\Application;
use APP\facades\Repo;
use PKP\core\Core;
use PKP\security\Validation;
use PKP\log\event\PKPSubmissionEventLogEntry;
use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;

class ScieloScreeningHandler extends Handler
{
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

    public function getScreeningData($submission)
    {
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());

        $dataScreening = array_merge(
            $this->getStatusAuthors($submission),
            $this->getStatusMetadataEnglish($submission),
            $this->getStatusPDFs($submission)
        );

        if (in_array(false, $dataScreening, true)) {
            $dataScreening['errorsScreening'] = true;
        }

        return $dataScreening;
    }
}
