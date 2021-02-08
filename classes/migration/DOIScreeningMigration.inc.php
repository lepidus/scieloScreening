<?php

/**
 * @file classes/migration/DOIScreeningmigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DOIScreeningMigration
 * @brief Describe database table structures for the DOIScreening object
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class DOIScreeningMigration extends Migration {
    
    public function up() {
		// A DOI provided during the screening. Every publication should have two of this
		Capsule::schema()->create('doi_screening', function (Blueprint $table) {
			$table->bigInteger('doi_id')->autoIncrement();
			$table->bigInteger('submission_id');
			$table->string('doi_code', 255);
		});

    }
    
    public function down() {
		Capsule::schema()->drop('doi_screening');
	}
}