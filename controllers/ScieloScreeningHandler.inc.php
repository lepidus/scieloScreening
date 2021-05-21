<?php

import('classes.handler.Handler');
import('plugins.generic.scieloScreening.classes.DOIScreening');
import('plugins.generic.scieloScreening.classes.DOIScreeningDAO');
import('plugins.generic.scieloScreening.classes.ScreeningChecker');

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

    function checkAuthors($args, $request){
        $checker = new ScreeningChecker();
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($args['submissionId']);
        $authors = $submission->getAuthors();
        $numberAuthors = $args['numberAuthors'];
        $response = array();
        
        $response['statusNumberAuthors'] = ($numberAuthors != count($authors)) ? ("error") : ("success");
        
        $nameAuthors = array_map(function($author){
            return $author->getLocalizedGivenName() . $author->getLocalizedFamilyName();
        }, $authors);
        $response['statusUppercase'] = ($checker->checkHasUppercaseAuthors($nameAuthors)) ? ("error") : ("success");

        $orcidAuthors = array_map(function($author){
            return $author->getOrcid();
        }, $authors);
        
        $response['statusOrcid'] = ($checker->checkOrcidAuthors($orcidAuthors)) ? ('success') : ("error");

        return json_encode($response);
    }

    function checkNumberPdfs($args, $request){
        $checker = new ScreeningChecker();
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($args['submissionId']);
        $galleys = $submission->getGalleys();
        $response = array();

        $fileTypeGalleys = array_map(function($galley){
            return ($galley->getFileType());
        }, $galleys);

        if(!$checker->checkNumberPdfs($fileTypeGalleys)[0])
            $response['statusNumberPdfs'] = 'error';
        else
            $response['statusNumberPdfs'] = 'success';

        return json_encode($response);
    }

    public function validateDOI($args, $request){
        $checker = new ScreeningChecker();
        $responseCrossref = $checker->getFromCrossref($args['doiString']);

        if(!$checker->checkDoiCrossref($responseCrossref)) {
            $responseCodeFromDOI = get_headers("https://doi.org/" . $args['doiString'])[0];
            if(str_contains($responseCodeFromDOI, "302")){
                $response = [
                    'statusValidate' => 0,
                    'messageError' => __("plugins.generic.scieloScreening.doiCrossrefRequirement")
                ];    
            }
            else{
                $response = [
                    'statusValidate' => 0,
                    'messageError' => __("plugins.generic.scieloScreening.doiNotRegistered")
                ]; 
            }
            return json_encode($response);
        }

        $itemCrossref = $responseCrossref['message']['items'][0];
        $submission = Services::get('submission')->get((int)$args['submissionId']);
        $authorSubmission = $submission->getAuthors()[0];
        $authorSubmission = $authorSubmission->getGivenName('en_US') . ' ' .  $authorSubmission->getFamilyName('en_US');
        $authorsCrossref = $itemCrossref['author'];

        if(!$checker->checkDoiFromAuthor($authorSubmission, $authorsCrossref)){
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiFromAuthor")
            ];
            return json_encode($response);
        }

        if(!$checker->checkDoiArticle($itemCrossref)) {
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

    public function validateDoisFromScreening($args, $request){
        $checker = new ScreeningChecker();
        
        if($checker->checkDoiRepeated($args['dois'])){
            $response = [
                'statusValidateDois' => 0,
                'messageError' => __("plugins.generic.scieloScreening.doiDifferentRequirement")
            ];
            return json_encode($response);
        }

        $countOkay = array_count_values($args['doisOkay'])['true'];
        if($countOkay < 2) {
            $response = [
                'statusValidateDois' => 0,
                'messageError' => __("plugins.generic.scieloScreening.attentionRules")
            ];
            return json_encode($response);
        }
        else if($countOkay == 2){
            if($checker->checkDoisLastTwoYears($args['doisYears'])){
                $response = [
                    'statusValidateDois' => 0,
                    'messageError' => __("plugins.generic.scieloScreening.attentionRules")
                ];
                return json_encode($response);
            }
        }

        return json_encode(['statusValidateDois' => 1]);
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