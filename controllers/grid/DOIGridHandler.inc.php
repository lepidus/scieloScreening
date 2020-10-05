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

        $dois = $doiScreeningDAO->getBySubmissionId($args['submissionId']);

        if(count($dois) == 0){
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
        }
        
        return http_response_code(200);
    }
    
    function isUppercase($string){
        $stringTratada = str_replace(' ', '', $string);
        return ctype_upper($stringTratada);
    }

    function checkAuthors($args, $request){
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($args['submissionId']);
        error_log(print_r($submission, true));
        $authors = $submission->getAuthors();
        $numberAuthors = $args['numberAuthors'];
        $response = array();
        
        $response['statusNumberAuthors'] = ($numberAuthors != count($authors)) ? ("error") : ("success");
        
        $uppercaseOne = false;
        foreach($authors as $author){
            $authorName = $author->getLocalizedGivenName() . $author->getLocalizedFamilyName();
            if($this->isUppercase($authorName)){
                $uppercaseOne = true;
            }
        }

        $response['statusUppercase'] = ($uppercaseOne) ? ("error") : ("success");

        $orcidOne = false;
        foreach ($authors as $author){
            if($author->getOrcid() != ''){
                $orcidOne = true;
            }
        }
        
        $response['statusOrcid'] = ($orcidOne) ? ('success') : ("error");

        return json_encode($response);
    }
}