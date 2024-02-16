<?php

/**
 * @file plugins/generic/scieloScreening/classes/DOIScreeningDAO.php
 *
 * @class DOIScreeningDAO
 * @ingroup plugins_generic_scieloScreening
 *
 * Operations for retrieving and modifying DOIScreening objects.
 */

namespace APP\plugins\generic\scieloScreening\classes;

use APP\plugins\generic\scieloScreening\classes\DOIScreening;
use PKP\db\DAO;
use Illuminate\Support\Facades\DB;

class DOIScreeningDAO extends DAO
{
    public function getBySubmissionId(int $submissionId): array
    {
        $result = DB::table('doi_screening')
            ->where('submission_id', $submissionId)
            ->get();

        $dois = array();
        foreach ($result->toArray() as $row) {
            $dois[] = $this->fromRow(get_object_vars($row));
        }

        return $dois;
    }

    public function insertObject(DOIScreening $doiScreening)
    {
        $inserted = DB::table('doi_screening')->insert([
            'submission_id' => (int) $doiScreening->getSubmissionId(),
            'doi_code' => $doiScreening->getDOICode(),
            'confirmed_authorship' => $doiScreening->getConfirmedAuthorship()
        ]);
    }

    public function updateObject(DOIScreening $doiScreening)
    {
        DB::table('doi_screening')
            ->where('doi_id', (int) $doiScreening->getDOIId())
            ->update([
                'doi_code' => $doiScreening->getDOICode()
            ]);
    }

    public function fromRow($row)
    {
        $doiScreening = new DOIScreening();

        $doiScreening->setDOIId($row['doi_id']);
        $doiScreening->setSubmissionId($row['submission_id']);
        $doiScreening->setDOICode($row['doi_code']);
        if (is_null($row['confirmed_authorship'])) {
            $doiScreening->setConfirmedAuthorship(true);
        } else {
            $doiScreening->setConfirmedAuthorship($row['confirmed_authorship']);
        }

        return $doiScreening;
    }
}
