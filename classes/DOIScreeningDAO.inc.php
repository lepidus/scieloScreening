<?php

/**
 * @file plugins/generic/authorDOIScreening/classes/DOIScreeningDAO.inc.php
 *
 * @class DOIScreeningDAO
 * @ingroup plugins_generic_authorDOIScreening
 *
 * Operations for retrieving and modifying DOIScreening objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.authorDOIScreening.classes.DOIScreening');

class DOIScreeningDAO extends DAO {

	function getByDOIId($doiId) {
		$result = $this->retrieve(
			'SELECT * FROM doi_screening WHERE doi_id = ?',
			[$doiId]
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

    function getBySubmissionId($submissionId) {
		$result = $this->retrieve(
			'SELECT * FROM doi_screening WHERE submission_id = ?',
			[$submissionId]
		);
        $returner = (new DAOResultFactory($result, $this, '_fromRow'))->toArray();

        return $returner;
	}

	function insertObject($doiScreening) {
		$this->update(
			'INSERT INTO doi_screening (submission_id, doi_code) VALUES (?, ?)',
			array(
				(int) $doiScreening->getSubmissionId(),
				$doiScreening->getDOICode()
			)
		);
		$doiScreening->setId($this->getLastInsertId());
        
        return $doiScreening->getDOIId();
	}

    function updateObject($doiScreening) {
		$this->update(
			'UPDATE	doi_screening
			SET	doi_code = ?
			WHERE doi_id = ?',
			array(
                $doiScreening->getDOICode(),
                (int) $doiScreening->getDOIId()
			)
		);
	}

    function getLastInsertId() {
		return $this->_getInsertId('doi_screening', 'doi_id');
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
