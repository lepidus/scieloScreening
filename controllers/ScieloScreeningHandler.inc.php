<?php

import('classes.handler.Handler');
import('plugins.generic.authorDOIScreening.classes.DOIScreening');
import('plugins.generic.authorDOIScreening.classes.DOIScreeningDAO');

class ScieloScreeningHandler extends Handler {

    function addDOIs($args, $request){
        $doiScreeningDAO = new DOIScreeningDAO();

        $dois = $doiScreeningDAO->getBySubmissionId($args['submissionId']);

        if(count($dois) == 0){
            if($args[firstDOI] != ""){
                $firstDOI = new DOIScreening();
                $firstDOI->setSubmissionId($args['submissionId']);
                $firstDOI->setDOICode($args['firstDOI']);
                $doiScreeningDAO->insertObject($firstDOI);
            }
            
            if($args[secondDOI] != ""){
                $secondDOI = new DOIScreening();
                $secondDOI->setSubmissionId($args['submissionId']);
                $secondDOI->setDOICode($args['secondDOI']);
                $doiScreeningDAO->insertObject($secondDOI);
            }
    
            if($args[thirdDOI] != ""){
                $thirdDOI = new DOIScreening();
                $thirdDOI->setSubmissionId($args['submissionId']);
                $thirdDOI->setDOICode($args['thirdDOI']);
                $doiScreeningDAO->insertObject($thirdDOI);
            }
        }
        
        return http_response_code(200);
    }
    
    function isUppercase($string){
        $stringTratada = str_replace(' ', '', $string);
        return ctype_upper($stringTratada);
    }

    function checkAuthors($args, $request){
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($args['submissionId']);
        $authors = $submission->getAuthors();
        $numberAuthors = $args['numberAuthors'];
        $response = array();
        
        $response['statusNumberAuthors'] = ($numberAuthors != count($authors)) ? ("error") : ("success");
        
        $uppercaseOne = false;
        foreach($authors as $author){
            $authorName = $author->getLocalizedGivenName() . $author->getLocalizedFamilyName();
            if($this->isUppercase($authorName)){
                $uppercaseOne = true;
            }
        }

        $response['statusUppercase'] = ($uppercaseOne) ? ("error") : ("success");

        $orcidOne = false;
        foreach ($authors as $author){
            if($author->getOrcid() != ''){
                $orcidOne = true;
            }
        }
        
        $response['statusOrcid'] = ($orcidOne) ? ('success') : ("error");

        return json_encode($response);
    }

    function checkNumberPdfs($args, $request){
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($args['submissionId']);
        $response = array();

        $numPDFs = 0;
        if(count($submission->getGalleys()) > 0) {
            foreach ($submission->getGalleys() as $galley) {
                if(strtolower($galley->getLabel()) == 'pdf'){
                    $numPDFs++;
                }
            }
        }

        if($numPDFs == 0 || $numPDFs > 1) {
            $response['statusNumberPdfs'] = 'error';
        }
        else {
            $response['statusNumberPdfs'] = 'success';
        }

        return json_encode($response);
    }

    private function getStatusDOI($submission) {
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());

        return [
            'statusDOI' => (count($dois) > 0),
            'dois' => $dois
        ];
    }

    private function getStatusAuthors($submission) {
        $authors = $submission->getAuthors();
        $statusAf = true;
        $statusOrcid = false;
        $listAuthors = array();
        
        foreach ($authors as $author) {   
            if($author->getLocalizedAffiliation() == ""){
                $statusAf = false;
                $listAuthors[] = $author->getLocalizedGivenName() . " " . $author->getLocalizedFamilyName();
            }
            if($author->getOrcid() != ''){
                $statusOrcid = true;
            }
        }

        return [
            'statusAffiliation' => $statusAf,
            'statusOrcid' => $statusOrcid,
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
        $numPDFs = 0;
        if(count($submission->getGalleys()) > 0) {
            foreach ($submission->getGalleys() as $galley) {
                if(strtolower($galley->getLabel()) == 'pdf'){
                    $numPDFs++;
                }
            }
        }
        $statusPDFs = ($numPDFs != 1);

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