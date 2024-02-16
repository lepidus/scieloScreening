<?php

/**
 * @file classes/migration/DOIScreeningmigration.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DOIScreeningMigration
 * @brief Describe database table structures for the DOIScreening object
 */

namespace APP\plugins\generic\scieloScreening\classes\migration;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as Schema;

class DOIScreeningMigration extends Migration
{
    public function up()
    {
        if (Schema::hasTable('doi_screening')) {
            Schema::table('doi_screening', function (Blueprint $table) {
                $table->boolean('confirmed_authorship')->nullable();
            });
        } else {
            // A DOI provided during the screening. Every publication should have at least two of this
            Schema::create('doi_screening', function (Blueprint $table) {
                $table->bigInteger('doi_id')->autoIncrement();
                $table->bigInteger('submission_id');
                $table->string('doi_code', 255);
                $table->boolean('confirmed_authorship')->nullable();
            });
        }
    }
}
