<?php

import('classes.handler.Handler');
import('plugins.generic.scieloScreening.classes.DOIScreening');
import('plugins.generic.scieloScreening.classes.DOIScreeningDAO');
import('plugins.generic.scieloScreening.classes.ScreeningChecker');
import('plugins.generic.scieloScreening.classes.DOIService');
import('plugins.generic.scieloScreening.classes.DOIOrgService');
import('plugins.generic.scieloScreening.classes.CrossrefService');
import('plugins.generic.scieloScreening.classes.DOISystemClient');

class ScieloScreeningHandler extends Handler {

    function addDOIs($args, $request){
        $doiScreeningDAO = new DOIScreeningDAO();

        $dois = $doiScreeningDAO->getBySubmissionId($args['submissionId']);

        if(count($dois) == 0){
            foreach($args['doisToSave'] as $doi){
                if($doi){
                    $doiObj = new DOIScreening();
                    $doiObj->setSubmissionId($args['submissionId']);
                    $doiObj->setDOICode($doi);
                    $doiScreeningDAO->insertObject($doiObj);
                }
            }
        }
        
        return http_response_code(200);
    }

    public function validateDOI($args, $request){
        $checker = new ScreeningChecker();
        $responseCrossref = array();

        $crossrefClient = new DOISystemClient('Crossref.org', 'https://api.crossref.org/works?filter=doi:');
        $crossrefService = new CrossrefService($args['doiString'], $crossrefClient);

        if (!$crossrefService->DOIExists()) {
            $statusMessage = $crossrefService->getStatusResponseMessage();
            $response = $this->getDOIStatusResponseMessage($statusMessage);
            return json_encode($response);
        } else {
            $responseCrossref = $crossrefService->getResponseContent();
        }
        
        if(!$checker->checkCrossrefResponse($responseCrossref)) {
            $doiOrgClient = new DOISystemClient('DOI.org', 'https://doi.org/');
            $doiOrgService = new DOIOrgService($args['doiString'], $doiOrgClient);
            $statusMessage = $doiOrgService->getStatusResponseMessage();
            $response = $this->getDOIStatusResponseMessage($statusMessage);
            return json_encode($response);
        }

        $itemCrossref = $responseCrossref['message']['items'][0];
        $submission = Services::get('submission')->get((int)$args['submissionId']);
        $authorSubmission = $submission->getAuthors()[0];
        $authorSubmission = $authorSubmission->getGivenName('en_US') . ' ' .  $authorSubmission->getFamilyName('en_US');
        $authorsCrossref = $itemCrossref['author'];

        if(!$checker->checkDOIFromAuthor($authorSubmission, $authorsCrossref)){
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiFromAuthor")
            ];
            return json_encode($response);
        }

        if(!$checker->checkDOIArticle($itemCrossref)) {
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiFromJournal")
            ];
            return json_encode($response);
        }

        $yearArticle = 0;
        if(isset($itemCrossref['published-print']))
            $yearArticle = $itemCrossref['published-print']['date-parts'][0][0];
        else
            $yearArticle = $itemCrossref['published-online']['date-parts'][0][0];

        return json_encode([
            'statusValidate' => 1,
            'yearArticle' => $yearArticle
        ]);
    }

    private function getDOIStatusResponseMessage($statusMessage) {
        return [
            'statusValidate' => DOIService::VALIDATION_ERROR_STATUS,
            'messageError' => __($statusMessage['key'], $statusMessage['params'])
        ];
    }

    public function validateDOIsFromScreening($args, $request){
        $checker = new ScreeningChecker();
        
        if($checker->checkDOIRepeated($args['dois'])){
            $response = [
                'statusValidateDOIs' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiDifferentRequirement")
            ];
            return json_encode($response);
        }

        $countOkay = array_count_values($args['doisOkay'])['true'];
        if($countOkay < 2) {
            $response = [
                'statusValidateDOIs' => 0,
                'messageError' => __("plugins.generic.scieloScreening.attentionRules")
            ];
            return json_encode($response);
        }
        else if($countOkay == 2){
            if($checker->checkDOIsLastTwoYears($args['doisYears'])){
                $response = [
                    'statusValidateDOIs' => 0,
                    'messageError' => __("plugins.generic.scieloScreening.attentionRules")
                ];
                return json_encode($response);
            }
        }

        return json_encode(['statusValidateDOIs' => 1]);
    }

    private function getStatusDOI($submission) {
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());

        return [
            'statusDOI' => (count($dois) > 0),
            'dois' => $dois
        ];
    }

    public function getStatusAuthors($submission) {
        $checker = new ScreeningChecker();
        $authors = $submission->getAuthors();
        
        $affiliationAuthors = array_map(function($author){
            return $author->getLocalizedAffiliation();
        }, $authors);
        $nameAuthors = array_map(function($author){
            return $author->getLocalizedGivenName() . " " . $author->getLocalizedFamilyName();
        }, $authors);
        $orcidAuthors = array_map(function($author){
            return $author->getOrcid();
        }, $authors);

        list($statusAf, $listAuthors) = $checker->checkAffiliationAuthors($affiliationAuthors, $nameAuthors);
        return [
            'statusAffiliation' => $statusAf,
            'statusOrcid' => $checker->checkOrcidAuthors($orcidAuthors),
            'listAuthors' => $listAuthors
        ];
    }

    private function getStatusMetadataEnglish($submission) {
        $publication = $submission->getCurrentPublication();
        $metadataList = array('title', 'abstract', 'keywords');
        $statusMetadataEnglish = true;
        $textMetadata = "";
        
        foreach ($metadataList as $metadata) {
            if($publication->getData($metadata, 'en_US') == "") {
                $statusMetadataEnglish = false;

                if($textMetadata != "") $textMetadata .= ", ";
                $textMetadata .= __("common." . $metadata);
            }
        }
        
        return [
            'statusMetadataEnglish' => $statusMetadataEnglish,
            'textMetadata' => $textMetadata
        ];
    }

    private function getStatusPDFs($submission) {
        $checker = new ScreeningChecker();
        $galleys = $submission->getGalleys();
        
        $fileTypeGalleys = array_map(function($galley){
            return ($galley->getFileType());
        }, $galleys);

        list($statusPDFs, $numPDFs) = $checker->checkNumberPdfs($fileTypeGalleys);

        return [
            'statusPDFs' => $statusPDFs,
            'numPDFs' => $numPDFs
        ];
    }

    public function getScreeningData($submission){
        $dataScreening = array_merge(
            $this->getStatusDOI($submission),
            $this->getStatusAuthors($submission),
            $this->getStatusMetadataEnglish($submission),
            $this->getStatusPDFs($submission)
        );
        
        if(in_array(false, $dataScreening, true)) {
            $dataScreening['errorsScreening'] = true;
        }

        return $dataScreening;
    }
}