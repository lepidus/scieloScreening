<?php

/**
 * @file plugins/generic/scieloScreening/classes/DOIScreeningDAO.inc.php
 *
 * @class DOIScreeningDAO
 * @ingroup plugins_generic_scieloScreening
 *
 * Operations for retrieving and modifying DOIScreening objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.scieloScreening.classes.DOIScreening');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Collection;

class DOIScreeningDAO extends DAO {

    function getBySubmissionId($submissionId) {
		$result = Capsule::table('doi_screening')
		->where('submission_id', $submissionId)
		->get();
		
		$returner = array();
		foreach($result->toArray() as $row) {
			$returner[] = $this->_fromRow($row);
		}

        return $returner;
	}

	function insertObject($doiScreening) {
		$inserted = Capsule::table('doi_screening')
		->insert([
			'submission_id' => (int) $doiScreening->getSubmissionId(),
			'doi_code' => $doiScreening->getDOICode()
		]);	
	}

    function updateObject($doiScreening) {
		Capsule::table('doi_screening')
		->where('doi_id', (int) $doiScreening->getDOIId())
		->update([
			'doi_code' => $doiScreening->getDOICode()
		]);
	}
    
    function _fromRow($row) {
        $doiScreening = new DOIScreening();
        
		$doiScreening->setDOIId($row['doi_id']);
		$doiScreening->setSubmissionId($row['submission_id']);
		$doiScreening->setDOICode($row['doi_code']);

		return $doiScreening;
	}
}

?>
