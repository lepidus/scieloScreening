<?php

// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function ($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'screeningchecker' => '/classes/ScreeningChecker.inc.php',
                'doiservice' => '/classes/DOIService.inc.php',
                'doisystemservice' => '/classes/DOISystemService.inc.php',
                'crossrefservice' => '/classes/CrossrefService.inc.php',
                'doisystemclient' => '/classes/DOISystemClient.inc.php',
                'doisystemclientfortests' => '/tests/DOISystemClientForTests.inc.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd
