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
    public const CHECK_STATUS_OKAY = 'Okay';
    public const CHECK_STATUS_NOT_OKAY = 'NotOkay';
    public const CHECK_STATUS_SKIPPED = 'Skipped';

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

    public function checkCreditAuthors($authorsCreditRoles)
    {
        $allNull = true;
        foreach ($authorsCreditRoles as $creditRoles) {
            if (!is_null($creditRoles)) {
                $allNull = false;
                break;
            }
        }

        if ($allNull || count($authorsCreditRoles) == 1) {
            return self::CHECK_STATUS_SKIPPED;
        }

        foreach ($authorsCreditRoles as $creditRoles) {
            if (empty($creditRoles)) {
                return self::CHECK_STATUS_NOT_OKAY;
            }
        }
        return self::CHECK_STATUS_OKAY;
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
