
<?php

class DOISystemClientForTests implements DOISystemClient {
    
    private $expectedStatus;
    private $shouldGenerateExceptions;

    function __construct($expectedStatus, $shouldGenerateExceptions = false) {
        $this->expectedStatus = $expectedStatus;
        $this->shouldGenerateExceptions = $shouldGenerateExceptions;
    }

    function getDoiStatus($doi) {
        if ($this->shouldGenerateExceptions) {
            throw new Exception("Failure to communicate with the DOI.org Server");
        }

        return $this->expectedStatus;
    }
}