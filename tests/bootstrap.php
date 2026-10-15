<?php

// Run the plugin's unit tests with installed OPS dependencies, without starting OPS or MySQL.
if (!defined('PKP_STRICT_MODE')) {
    define('PKP_STRICT_MODE', true);
}
require dirname(__DIR__, 4) . '/lib/pkp/lib/vendor/autoload.php';
