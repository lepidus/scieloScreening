<?php
/**
 * @defgroup plugins_generic_authorDOIScreening
 */
/**
 * @file plugins/generic/authorDOIScreening/index.php
 *
 * @ingroup plugins_generic_returningAuthorScreening
 * @brief Wrapper for the Author DOI Screening plugin.
 *
 */
require_once('AuthorDOIScreeningPlugin.inc.php');
return new AuthorDOIScreeningPlugin();