<?php

class DOISystemClient {

    public function getDOIStatus($serverUrl, $doi) {
        $doiStatus = get_headers($serverUrl . $doi)[0];
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