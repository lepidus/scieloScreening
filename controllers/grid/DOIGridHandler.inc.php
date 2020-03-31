<?php

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.authorDOIScreening.classes.DOIScreening');
import('plugins.generic.authorDOIScreening.classes.DOIScreeningDAO');

class DOIGridHandler extends GridHandler {

    static $plugin;

    /**
	 * Set the DOI plugin.
	 * @param $plugin AuthorDOIScreeningPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

    function updateDOIs($args, $request){
        $doiScreeningDAO = new DOIScreeningDAO();
        $firstDOI = new DOIScreening();
        $secondDOI = new DOIScreening();

        $firstDOI->setDOIId($args['firstDOIId']);
        $firstDOI->setDOICode($args['firstDOI']);
        $secondDOI->setDOIId($args['secondDOIId']);
        $secondDOI->setDOICode($args['secondDOI']);

        $doiScreeningDAO->updateObject($firstDOI);
        $doiScreeningDAO->updateObject($secondDOI);

        return http_response_code(200);
    }

    function addDOIs($args, $request){
        $doiScreeningDAO = new DOIScreeningDAO();
        $firstDOI = new DOIScreening();
        $secondDOI = new DOIScreening();

        $firstDOI->setSubmissionId($args['submissionId']);
        $firstDOI->setDOICode($args['firstDOI']);
        $secondDOI->setSubmissionId($args['submissionId']);
        $secondDOI->setDOICode($args['secondDOI']);

        $doiScreeningDAO->insertObject($firstDOI);
        $doiScreeningDAO->insertObject($secondDOI);

        return http_response_code(200);
    }
}