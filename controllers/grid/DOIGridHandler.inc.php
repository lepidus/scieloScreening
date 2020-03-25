<?php

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.funding.controllers.grid.FunderGridRow');
import('plugins.generic.funding.controllers.grid.FunderGridCellProvider');

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
        error_log(print_r($args, TRUE));
        return true;
    }
}