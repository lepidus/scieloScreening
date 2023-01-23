<?php

/**
 * @file plugins/generic/scieloScreening/classes/DOIScreening.inc.php
 *
 * @class DOIScreening
 * @ingroup plugins_generic_scieloScreening
 *
 * Data object representing a DOI provided during the screening
 */

class DOIScreening extends DataObject
{
    public function getDOIId()
    {
        return $this->getData('doiId');
    }

    public function setDOIId($doiId)
    {
        return $this->setData('doiId', $doiId);
    }

    public function getSubmissionId()
    {
        return $this->getData('submissionId');
    }

    public function setSubmissionId($submissionId)
    {
        return $this->setData('submissionId', $submissionId);
    }

    public function getDOICode()
    {
        return $this->getData('doiCode');
    }

    public function setDOICode($doiCode)
    {
        return $this->setData('doiCode', $doiCode);
    }

    public function getConfirmedAuthorship()
    {
        return $this->getData('confirmedAuthorship');
    }

    public function getConfirmedAuthorshipString()
    {
        if ($this->getData('confirmedAuthorship')) {
            return __('plugins.generic.scieloScreening.authorshipConfirmed');
        }

        return __('plugins.generic.scieloScreening.authorshipNotConfirmed');
    }

    public function setConfirmedAuthorship($statusAuthorship)
    {
        return $this->setData('confirmedAuthorship', $statusAuthorship);
    }
}
