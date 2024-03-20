<?php

/**
 * @file plugins/generic/scieloScreening/classes/ScreeningChecker.php
 *
 * @class ScreeningChecker
 * @ingroup plugins_generic_scieloScreening
 *
 * Object to execute a series of verifications that are used by the plugin
 */

namespace APP\plugins\generic\scieloScreening\classes;

class ScreeningChecker
{
    public function isUppercase($string)
    {
        $formattedString = str_replace(' ', '', $string);
        return ctype_upper($formattedString);
    }

    public function checkHasUppercaseAuthors($nameAuthors)
    {
        $uppercaseOne = false;
        foreach ($nameAuthors as $authorName) {
            if ($this->isUppercase($authorName)) {
                $uppercaseOne = true;
            }
        }
        return $uppercaseOne;
    }

    public function checkOrcidAuthors($authorsOrcid)
    {
        $orcidOne = false;
        foreach ($authorsOrcid as $orcid) {
            if ($orcid != '') {
                $orcidOne = true;
            }
        }
        return $orcidOne;
    }

    public function checkNumberPdfs($fileTypeGalleys)
    {
        $numPDFs = 0;
        if (count($fileTypeGalleys) > 0) {
            foreach ($fileTypeGalleys as $galleyType) {
                if ($galleyType == 'application/pdf') {
                    $numPDFs++;
                }
            }
        }

        return [$numPDFs == 1, $numPDFs];
    }

    public function checkAffiliationAuthors($affiliationAuthors, $nameAuthors)
    {
        $statusAffiliation = true;
        $authorsNoAffiliation = array();
        for ($i = 0; $i < count($nameAuthors); $i++) {
            if ($affiliationAuthors[$i] == "") {
                $statusAffiliation = false;
                $authorsNoAffiliation[] = $nameAuthors[$i];
            }
        }

        return [$statusAffiliation, $authorsNoAffiliation];
    }
}
