<?php

require ("DOISystemClient.inc.php");

class DOISystemClientForDOIORGResponse implements DOISystemClient 
{
    private $doiHttpStatus;

    public function getDOIStatus($doi) {
        $doiStatus = get_headers(CrossrefNonExistentDOI::DOI_ORG_BASE_URL . $doi)[0];
        $doiStatusErrorCode = $this->getHTTPErrorCodeByHTTPStatus($doiStatus);
        return $doiStatusErrorCode;
    }

    public function getHTTPErrorCodeByHTTPStatus($doiStatus) {
        $errorCodeArrayKeyByPartitionedResponse = 1;
        $httpErrorCodeLength = 3;

        $httpPartitionedResponse = explode(" ", $doiStatus);
        $httpErrorCodePartition = $httpPartitionedResponse[$errorCodeArrayKeyByPartitionedResponse];

        if (strlen($httpErrorCodePartition) === $httpErrorCodeLength) {
            $httpIntegerErrorCodeByResponse = intval($httpErrorCodePartition);
            return $httpIntegerErrorCodeByResponse;
        }

        return CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS;
    }
}