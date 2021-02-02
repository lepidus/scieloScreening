<?php
/**
 * @defgroup plugins_generic_scieloScreening
 */
/**
 * @file plugins/generic/scieloScreening/index.php
 *
 * @ingroup plugins_generic_returningAuthorScreening
 * @brief Wrapper for the Author DOI Screening plugin.
 *
 */
require_once('ScieloScreeningPlugin.inc.php');
return new ScieloScreeningPlugin();