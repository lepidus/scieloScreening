<?php

/**
 * @file plugins/generic/authorDOIScreening/classes/ScreeningChecker.inc.php
 *
 * @class ScreeningChecker
 * @ingroup plugins_generic_authorDOIScreening
 *
 * Object to execute a series of verifications that are used by the plugin
 */

class ScreeningChecker {
    public function isUppercase($string){
        $stringTratada = str_replace(' ', '', $string);
        return ctype_upper($stringTratada);
    }

    public function checkHasUppercaseAuthors($nameAuthors){
        $uppercaseOne = false;
        foreach($nameAuthors as $authorName) {
            if($this->isUppercase($authorName)){
                $uppercaseOne = true;
            }
        }
        return $uppercaseOne;
    }

    public function checkOrcidAuthors($authorsOrcid){
        $orcidOne = false;
        foreach ($authorsOrcid as $orcid){
            if($orcid != ''){
                $orcidOne = true;
            }
        }
        return $orcidOne;
    }

    public function checkNumberPdfs($labelGalleys){
        $numPDFs = 0;
        if(count($labelGalleys) > 0) {
            foreach ($labelGalleys as $galley) {
                if($galley == 'pdf')
                    $numPDFs++;
            }
        }

        return [$numPDFs == 1, $numPDFs];
    }

    public function getFromCrossref($doiString){
        $response = file_get_contents('https://api.crossref.org/works?filter=doi:' . $doiString);
        $johnson = json_decode($response, true);

        return $johnson;
    }

    public function checkDoiCrossref($responseCrossref) {
        $status = $responseCrossref['status'];
        $items = $responseCrossref['message']['items'];

        return $status == 'ok' || !empty($items);
    }

    public function checkDoiFromAuthor($authorSubmission, $authorsCrossref) {
        $foundAuthor = false;
        for($i = 0; $i < count($authorsCrossref); $i++){
            $nameCrossref = $authorsCrossref[$i]['given'] . $authorsCrossref[$i]['family'];
            similar_text($nameCrossref, $authorSubmission, $similarity);

            if($similarity > 35){
                $foundAuthor = true;
                break;
            }
        }

        return $foundAuthor;
    }

    public function checkDoiArticle($itemCrossref) {
        return $itemCrossref['type'] == 'journal-article';
    }

    public function checkDoiRepeated($dois) {
        return $dois[0] == $dois[1] || $dois[0] == $dois[2] || $dois[1] == $dois[2];
    }

    public function checkDoisLastTwoYears($doisYears) {
        $countDoisOkay = 0;
        $currentYear = date('Y');
        foreach($doisYears as $doiYear){
            if((int)$doiYear >= (int)$currentYear - 2) $countDoisOkay++;
        }
        
        return $countDoisOkay == 2;
    }

    public function checkAffiliationAuthors($affiliationAuthors, $nameAuthors) {
        $statusAf = true;
        $authorsNoAffiliation = array();
        for($i = 0; $i < count($nameAuthors); $i++) {
            if($affiliationAuthors[$i] == ""){
                $statusAf = false;
                $authorsNoAffiliation[] = $nameAuthors[$i];
            }
        }

        return [$statusAf, $authorsNoAffiliation];
    }

}