
<?php

class DOISystemClientForTests extends DOISystemClient {
    
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

    public function getHTTPErrorCodeByHTTPStatus ($httpStatusFromDOI) {
        $errorCodeArrayKeyByPartitionedResponse = 1;
        $httpErrorCodeLength = 3;

        $httpPartitionedResponse = explode(" ", $httpStatusFromDOI);
        $httpErrorCodePartition = $httpPartitionedResponse[$errorCodeArrayKeyByPartitionedResponse];

        if (strlen($httpErrorCodePartition) === $httpErrorCodeLength) {
            $httpIntegerErrorCodeByResponse = intval($httpErrorCodePartition);
            return $httpIntegerErrorCodeByResponse;
        }

        return CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS;
    }
}