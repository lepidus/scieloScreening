
<?php

class DOISystemClientForTests extends DOISystemClient {
    
    private $expectedStatus;
    private $shouldGenerateExceptions;

    function __construct($expectedStatus, $shouldGenerateExceptions = false) {
        $this->expectedStatus = $expectedStatus;
        $this->shouldGenerateExceptions = $shouldGenerateExceptions;
    }

    function getDOIStatus($doi) {
        if ($this->shouldGenerateExceptions) {
            throw new Exception("Failure to communicate with the DOI.org Server");
        }

        return $this->expectedStatus;
    }
}