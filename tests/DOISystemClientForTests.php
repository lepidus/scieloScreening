<?php

namespace APP\plugins\generic\scieloScreening\tests;

use APP\plugins\generic\scieloScreening\classes\DOISystemClient;

class DOISystemClientForTests extends DOISystemClient
{
    private $expectedStatus;
    private $shouldGenerateExceptions;

    public function __construct($server, $serverUrl, $expectedStatus, $shouldGenerateExceptions = false)
    {
        $this->expectedStatus = $expectedStatus;
        $this->shouldGenerateExceptions = $shouldGenerateExceptions;

        parent::__construct($server, $serverUrl);
    }

    public function getDOIStatus($doi)
    {
        if ($this->shouldGenerateExceptions) {
            throw new \Exception("Failure to communicate with the " . $this->server . " Server");
        }

        return $this->expectedStatus;
    }
}
