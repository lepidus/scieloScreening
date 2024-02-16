<?php

namespace APP\plugins\generic\scieloScreening\classes;

use APP\plugins\generic\scieloScreening\classes\DOIService;

class DOISystemClient
{
    private $server;
    private $serverUrl;

    public function __construct($server, $serverUrl)
    {
        $this->server = $server;
        $this->serverUrl = $serverUrl;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getServerUrl()
    {
        return $this->serverUrl;
    }

    public function getDOIStatus($doi)
    {
        $doiStatus = get_headers($this->getServerUrl() . $doi)[0];
        $doiStatusErrorCode = $this->getHTTPErrorCodeByHTTPStatus($doiStatus);
        return $doiStatusErrorCode;
    }

    public function getDOIResponse($doi)
    {
        $response = file_get_contents($this->getServerUrl() . $doi);
        $jsonResponse = json_decode($response, true);

        return $jsonResponse;
    }

    public function getHTTPErrorCodeByHTTPStatus($doiStatus)
    {
        $errorCodeArrayKeyByPartitionedResponse = 1;
        $httpErrorCodeLength = 3;

        $httpPartitionedResponse = explode(" ", $doiStatus);
        $httpErrorCodePartition = $httpPartitionedResponse[$errorCodeArrayKeyByPartitionedResponse];

        if (strlen($httpErrorCodePartition) === $httpErrorCodeLength) {
            $httpIntegerErrorCodeByResponse = intval($httpErrorCodePartition);
            return $httpIntegerErrorCodeByResponse;
        }

        return DOIService::VALIDATION_ERROR_STATUS;
    }
}
