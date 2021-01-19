<?php

import('classes.handler.Handler');
import('plugins.generic.authorDOIScreening.classes.DOIScreening');
import('plugins.generic.authorDOIScreening.classes.DOIScreeningDAO');

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
    
    private function isUppercase($string){
        $stringTratada = str_replace(' ', '', $string);
        return ctype_upper($stringTratada);
    }

    private function getFromCrossref($doiString){
        $response = file_get_contents('https://api.crossref.org/works?filter=doi:' . $doiString);
        $johnson = json_decode($response, true);

        return $johnson;
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

    public function validateDOI($args, $request){
        $responseCrossref = $this->getFromCrossref($args['doiString']);
        $status = $responseCrossref['status'];
        $items = $responseCrossref['message']['items'];

        if($status != 'ok' || empty($items)) {
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.authorDOIScreening.doiCrossrefRequirement")
            ];
            return json_encode($response);
        }

        $submission = Services::get('submission')->get((int)$args['submissionId']);
        $authorSubmission = $submission->getAuthors()[0];
        $authorsCrossref = $items[0]['author'];
        $foundAuthor = false;
        for($i = 0; $i < count($authorsCrossref); $i++){
            $name1 = $authorsCrossref[$i]['given'] . $authorsCrossref[$i]['family'];
            $name2 = $authorSubmission->getGivenName('en_US') . $authorSubmission->getFamilyName('en_US');
            similar_text($name1, $name2, $similarity);

            if($similarity > 35){
                $foundAuthor = true;
                break;
            }
        }

        if(!$foundAuthor){
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.authorDOIScreening.doiFromAuthor")
            ];
            return json_encode($response);
        }

        $doiType = $items[0]['type'];
        if($doiType != 'journal-article') {
            $response = [
                'statusValidate' => 0,
                'messageError' => __("plugins.generic.authorDOIScreening.doiFromJournal")
            ];
            return json_encode($response);
        }

        $yearArticle = 0;
        if(isset($items[0]['published-print']))
            $yearArticle = $items[0]['published-print']['date-parts'][0][0];
        else
            $yearArticle = $items[0]['published-online']['date-parts'][0][0];

        return json_encode([
            'statusValidate' => 1,
            'yearArticle' => $yearArticle
        ]);
    }

    public function validateDoisFromScreening($args, $request){
        $dois = $args['dois'];
        
        if($dois[0] == $dois[1] || $dois[0] == $dois[2] || $dois[1] == $dois[2]){
            $response = [
                'statusValidateDois' => 0,
                'messageError' => __("plugins.generic.authorDOIScreening.doiDifferentRequirement")
            ];
            return json_encode($response);
        }

        $countOkay = array_count_values($args['doisOkay'])['true'];
        if($countOkay < 2) {
            $response = [
                'statusValidateDois' => 0,
                'messageError' => __("plugins.generic.authorDOIScreening.attentionRules")
            ];
            return json_encode($response);
        }
        else if($countOkay == 2){
            $countAnos = 0;
            $anoAtual = date('Y');
            foreach($args['doisYears'] as $doiYear){
                if((int)$doiYear >= (int)$anoAtual - 2) $countAnos++;
            }

            if($countAnos < 2){
                $response = [
                    'statusValidateDois' => 0,
                    'messageError' => __("plugins.generic.authorDOIScreening.attentionRules")
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