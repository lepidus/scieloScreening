<?php

/**
 * @file plugins/generic/scieloScreening/classes/ScreeningChecker.inc.php
 *
 * @class ScreeningChecker
 * @ingroup plugins_generic_scieloScreening
 *
 * Object to execute a series of verifications that are used by the plugin
 */

class ScreeningChecker {

    public function isUppercase($string){
        $formattedString = str_replace(' ', '', $string);
        return ctype_upper($formattedString);
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

    public function checkNumberPdfs($fileTypeGalleys){
        $numPDFs = 0;
        if(count($fileTypeGalleys) > 0) {
            foreach ($fileTypeGalleys as $galleyType) {
                if($galleyType == 'application/pdf')
                    $numPDFs++;
            }
        }

        return [$numPDFs == 1, $numPDFs];
    }

    public function getFromCrossref($doiString){
        $response = file_get_contents('https://api.crossref.org/works?filter=doi:' . $doiString);
        $jsonResponse = json_decode($response, true);

        return $jsonResponse;
    }

    public function checkDoiCrossref($responseCrossref) {
        $status = $responseCrossref['status'];
        $items = $responseCrossref['message']['items'];

        return $status == 'ok' && !empty($items);
    }

    public function checkDoiFromAuthor($authorSubmission, $authorsCrossref) {
        $foundAuthor = false;
        $wordCount = 2;

        for($i = 0; $i < count($authorsCrossref); $i++)
            $nameAuthorCrossref = $authorsCrossref[$i]['given'] . " " . $authorsCrossref[$i]['family'];

            $tokensAuthorSubmission = explode(" ", $authorSubmission);
            $tokensAuthorCrossref = explode(" ", $nameAuthorCrossref);
            
            $firstNameAuthorSubmission = $tokensAuthorSubmission[0];
            $firstNameAuthorCrossref = $tokensAuthorCrossref[0];

            if((strcasecmp($firstNameAuthorSubmission, $firstNameAuthorCrossref) == 0)){
                if (sizeof($tokensAuthorSubmission) == sizeof($tokensAuthorCrossref)){
                    $foundAuthor = $this->checkAuthorNameTokens($tokensAuthorSubmission,$tokensAuthorCrossref);
                }
                else{ 
                    if (sizeof($tokensAuthorCrossref) == $wordCount){
                        $foundAuthor = $this->checkAuthorNameTokensWithOneToken($tokensAuthorSubmission,$tokensAuthorCrossref);
                    }
                } 
            }
        }

        return $foundAuthor;
    }
    
    public function checkAuthorNameTokens($tokensAuthorSubmission,$tokensAuthorCrossref){
        $equalsName = false;
        $countNamesEquals = 0;

        for ($i=0; $i < sizeof($tokensAuthorSubmission); $i++) { 
            $abbreviation = $tokensAuthorSubmission[$i][0] . '.';
            if((strcasecmp($tokensAuthorSubmission[$i], $tokensAuthorCrossref[$i]) == 0) ||  (strcasecmp($abbreviation, $tokensAuthorCrossref[$i]) == 0)){
                $countNamesEquals+=1;
            }
        }

        if ($countNamesEquals == sizeof($tokensAuthorSubmission)){
            $equalsName = true;
        }

        return $equalsName;
    }

    public function checkAuthorNameTokensWithOneToken($tokensAuthorSubmission,$tokensAuthorCrossref){
        $equalsName = false;
        for ($i=1; $i < sizeof($tokensAuthorSubmission); $i++) { 
            $abbreviation = $tokensAuthorSubmission[$i][0] . '.';
            if((strcasecmp($tokensAuthorSubmission[$i], $tokensAuthorCrossref[1]) == 0) ||  (strcasecmp($abbreviation, $tokensAuthorCrossref[1]) == 0)){
                $equalsName = true;
            }
        }
        return $equalsName;
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