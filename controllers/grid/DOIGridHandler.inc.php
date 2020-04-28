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

    function addDOIs($args, $request){
        $doiScreeningDAO = new DOIScreeningDAO();
        
        if($args[firstDOI] != ""){
            $firstDOI = new DOIScreening();
            $firstDOI->setSubmissionId($args['submissionId']);
            $firstDOI->setDOICode($args['firstDOI']);
            $doiScreeningDAO->insertObject($firstDOI);
        }
        
        if($args[secondDOI] != ""){
            $secondDOI = new DOIScreening();
            $secondDOI->setSubmissionId($args['submissionId']);
            $secondDOI->setDOICode($args['secondDOI']);
            $doiScreeningDAO->insertObject($secondDOI);
        }

        if($args[thirdDOI] != ""){
            $thirdDOI = new DOIScreening();
            $thirdDOI->setSubmissionId($args['submissionId']);
            $thirdDOI->setDOICode($args['thirdDOI']);
            $doiScreeningDAO->insertObject($thirdDOI);
        }
        
        return http_response_code(200);
    }
}