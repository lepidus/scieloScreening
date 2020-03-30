<?php

/**
 * @file plugins/generic/authorDOIScreening/classes/DOIScreening.inc.php
 *
 * @class DOIScreening
 * @ingroup plugins_generic_authorDOIScreening
 *
 * Data object representing a DOI provided during the screening
 */

class DOIScreening extends DataObject {

    function getDOIId(){
        return $this->getData('doiId');
    }
    function setDOIId($doiId){
        return $this->setData('doiId', $doiId);
    }

	function getSubmissionId(){
		return $this->getData('submissionId');
	}
	function setSubmissionId($submissionId){
		return $this->setData('submissionId', $submissionId);
    }
    
    function getDOICode(){
        return $this->getData('doiCode');
    }
    function setDOICode($doiCode){
        return $this->setData('doiCode', $doiCode);
    }

}

?>
