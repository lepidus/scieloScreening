<?php

import('classes.handler.Handler');
import('classes.log.SubmissionEventLogEntry');
import('lib.pkp.classes.log.SubmissionLog');
import('plugins.generic.scieloScreening.classes.DOIScreening');
import('plugins.generic.scieloScreening.classes.DOIScreeningDAO');
import('plugins.generic.scieloScreening.classes.ScreeningChecker');
import('plugins.generic.scieloScreening.classes.DOIService');
import('plugins.generic.scieloScreening.classes.DOISystemService');
import('plugins.generic.scieloScreening.classes.CrossrefService');
import('plugins.generic.scieloScreening.classes.DOISystemClient');

class ScieloScreeningHandler extends Handler
{
    public function addDOIs($args, $request)
    {
        $doiScreeningDAO = new DOIScreeningDAO();

        $dois = $doiScreeningDAO->getBySubmissionId($args['submissionId']);

        if (count($dois) == 0) {
            foreach ($args['doisToSave'] as $doi) {
                if ($doi) {
                    $doiObj = new DOIScreening();
                    $doiObj->setSubmissionId($args['submissionId']);
                    $doiObj->setDOICode($doi[0]);
                    $doiObj->setConfirmedAuthorship((int) $doi[1]);
                    $doiScreeningDAO->insertObject($doiObj);
                }
            }
        }

        return http_response_code(200);
    }

    private function logDOIValidationEvent($request, $submission, $doi, $response)
    {
        if (array_key_exists('messageError', $response)) {
            SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'plugins.generic.scieloScreening.log.doiNotValidated', ['doi' => $doi, 'errorMessage' => $response['messageError']]);
        } elseif ($response['doiConfirmedAuthorship']) {
            SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'plugins.generic.scieloScreening.log.doiValidatedAuthorshipConfirmed', ['doi' => $doi]);
        } else {
            SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'plugins.generic.scieloScreening.log.doiValidatedAuthorshipNotConfirmed', ['doi' => $doi]);
        }
    }

    public function validateDOI($args, $request)
    {
        $checker = new ScreeningChecker();
        $responseCrossref = array();
        $submission = Services::get('submission')->get((int)$args['submissionId']);

        $crossrefClient = new DOISystemClient('Crossref.org', 'https://api.crossref.org/works?filter=doi:');
        $crossrefService = new CrossrefService($args['doiString'], $crossrefClient);

        if (!$crossrefService->DOIExists()) {
            $statusMessage = $crossrefService->getStatusResponseMessage();
            $response = $this->getDOIStatusResponseMessage($statusMessage);

            $this->logDOIValidationEvent($request, $submission, $args['doiString'], $response);
            return json_encode($response);
        } else {
            $responseCrossref = $crossrefService->getResponseContent();
        }

        if (!$checker->checkCrossrefResponse($responseCrossref)) {
            $doiOrgClient = new DOISystemClient('DOI.org', 'https://doi.org/');
            $doiOrgService = new DOISystemService($args['doiString'], $doiOrgClient);
            $statusMessage = $doiOrgService->getStatusResponseMessage();
            $response = $this->getDOIStatusResponseMessage($statusMessage);

            $this->logDOIValidationEvent($request, $submission, $args['doiString'], $response);
            return json_encode($response);
        }

        $itemCrossref = $responseCrossref['message']['items'][0];
        if (!$checker->checkDOIArticle($itemCrossref)) {
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiFromJournal")
            ];
            $this->logDOIValidationEvent($request, $submission, $args['doiString'], $response);
            return json_encode($response);
        }

        $yearArticle = 0;
        if (isset($itemCrossref['published-print'])) {
            $yearArticle = $itemCrossref['published-print']['date-parts'][0][0];
        } else {
            $yearArticle = $itemCrossref['published-online']['date-parts'][0][0];
        }

        $response = [
            'statusValidate' => 1,
            'yearArticle' => $yearArticle,
            'doiConfirmedAuthorship' => $this->checkDOIAuthorship($submission, $responseCrossref)
        ];

        $this->logDOIValidationEvent($request, $submission, $args['doiString'], $response);
        return json_encode($response);
    }

    public function checkDOIAuthorship($submission, $responseCrossref)
    {
        $itemCrossref = $responseCrossref['message']['items'][0];
        $authorsSubmission = $submission->getAuthors();
        $checker = new ScreeningChecker();

        foreach ($authorsSubmission as $authorSubmission) {
            $authorName = $authorSubmission->getLocalizedData('givenName', 'en_US') . ' ' .  $authorSubmission->getLocalizedData('familyName', 'en_US');
            $authorsCrossref = $itemCrossref['author'];

            if ($checker->checkDOIFromAuthor($authorName, $authorsCrossref)) {
                return true;
            }
        }

        return false;
    }

    private function getDOIStatusResponseMessage($statusMessage)
    {
        return [
            'statusValidate' => DOIService::VALIDATION_ERROR_STATUS,
            'messageError' => __($statusMessage['key'], $statusMessage['params'])
        ];
    }

    private function logDOIScreeningEvent($request, $submission, $doisImploded = null)
    {
        if (is_null($doisImploded)) {
            SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'plugins.generic.scieloScreening.log.doiScreeningCompletedSuccess');
        } else {
            SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'plugins.generic.scieloScreening.log.doiScreeningNotCompleted', ['doisImploded' => $doisImploded]);
        }
    }

    public function validateDOIsFromScreening($args, $request)
    {
        $checker = new ScreeningChecker();
        $submission = Services::get('submission')->get((int)$args['submissionId']);

        if ($checker->checkDOIRepeated($args['dois'])) {
            $response = [
                'statusValidateDOIs' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiDifferentRequirement")
            ];
            $this->logDOIScreeningEvent($request, $submission, implode(", ", $args['dois']));
            return json_encode($response);
        }

        $countOkay = array_count_values($args['doisOkay'])['true'];
        if ($countOkay < 2) {
            $response = [
                'statusValidateDOIs' => 0,
                'messageError' => __("plugins.generic.scieloScreening.attentionRules")
            ];
            $this->logDOIScreeningEvent($request, $submission, implode(", ", $args['dois']));
            return json_encode($response);
        } elseif ($countOkay == 2) {
            if (!$checker->checkDOIsLastTwoYears($args['doisYears'])) {
                $response = [
                    'statusValidateDOIs' => 0,
                    'messageError' => __("plugins.generic.scieloScreening.attentionRules")
                ];
                $this->logDOIScreeningEvent($request, $submission, implode(", ", $args['dois']));
                return json_encode($response);
            }
        }

        $this->logDOIScreeningEvent($request, $submission);
        return json_encode(['statusValidateDOIs' => 1]);
    }

    public function getStatusDOI($submission, $dois)
    {
        $statusDOI = (count($dois) > 0);
        $doisConfirmedAuthorship = true;
        $authorsDOIs = array();

        if ($statusDOI) {
            foreach ($dois as $doi) {
                if (!$doi->getConfirmedAuthorship()) {
                    $doisConfirmedAuthorship = false;
                    $statusDOI = false;
                }

                $responseCrossref = file_get_contents('https://api.crossref.org/works?filter=doi:'.$doi->getDOICode());
                $authorsDOIs[] = implode(", ", $this->getDoiAuthorsNames($responseCrossref));
            }
        }

        $authors = $submission->getCurrentPublication()->getData('authors');
        if (!empty($authors)) {
            $submissionAuthor = $authors[0];
            $subAuthorName = $submissionAuthor->getLocalizedData('givenName') . ' ' . $submissionAuthor->getLocalizedData('familyName');
        }

        return [
            'statusDOI' => $statusDOI,
            'dois' => $dois,
            'doisConfirmedAuthorship' => $doisConfirmedAuthorship,
            'authorFromSubmission' => $subAuthorName,
            'authorsFromDOIs' => $authorsDOIs
        ];
    }

    public function getStatusAuthors($submission)
    {
        $checker = new ScreeningChecker();
        $authors = $submission->getAuthors();

        $affiliationAuthors = array_map(function ($author) {
            return $author->getLocalizedAffiliation();
        }, $authors);
        $nameAuthors = array_map(function ($author) {
            return $author->getLocalizedGivenName() . " " . $author->getLocalizedFamilyName();
        }, $authors);
        $orcidAuthors = array_map(function ($author) {
            return $author->getOrcid();
        }, $authors);

        list($statusAf, $listAuthors) = $checker->checkAffiliationAuthors($affiliationAuthors, $nameAuthors);
        return [
            'statusAffiliation' => $statusAf,
            'statusOrcid' => $checker->checkOrcidAuthors($orcidAuthors),
            'listAuthors' => $listAuthors
        ];
    }

    private function getStatusMetadataEnglish($submission)
    {
        $publication = $submission->getCurrentPublication();
        $metadataList = array('title', 'abstract', 'keywords');
        $statusMetadataEnglish = true;
        $textMetadataScreening = "";

        foreach ($metadataList as $metadata) {
            if ($publication->getData($metadata, 'en_US') == "") {
                $statusMetadataEnglish = false;

                if ($textMetadataScreening != "") {
                    $textMetadataScreening .= ", ";
                }
                $textMetadataScreening .= __("common." . $metadata);
            }
        }

        return [
            'statusMetadataEnglish' => $statusMetadataEnglish,
            'textMetadataScreening' => $textMetadataScreening
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
            $this->getStatusDOI($submission, $dois),
            $this->getStatusAuthors($submission),
            $this->getStatusMetadataEnglish($submission),
            $this->getStatusPDFs($submission)
        );

        if (in_array(false, $dataScreening, true)) {
            $dataScreening['errorsScreening'] = true;
        }

        return $dataScreening;
    }

    public function getDoiAuthorsNames($response)
    {
        $decodeAPI = json_decode($response, true);

        $authors = $decodeAPI['message']['items'][0]['author'];
        $authorsNames = [];

        foreach ($authors as $author) {
            $given = $author['given'];
            $family = $author['family'];

            if (!empty($given) && !empty($family)) {
                $authorsNames[] = $given . " " . $family;
            }
        }

        return $authorsNames;
    }
}
