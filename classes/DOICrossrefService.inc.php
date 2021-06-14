<?php

class DOICrossrefService extends DOIService {

    const CROSSREF_STATUS_SUCCESS_CODE = 200;

    const CROSSREF_STATUS_DOI_INVALID_CODE = 400;
    const CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.doiCrossrefInvalidErrorCode';
    
    function __construct($doi, $doiClient) {
        parent::__construct($doi, $doiClient);

        $crossrefResponseStatus = [
            self::CROSSREF_STATUS_DOI_INVALID_CODE => 
            self::CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY,
        ];

        $this->responseStatusMapping += $crossrefResponseStatus;
    }

    public function getDOICrossrefStatusCode() {
        $doiClient = $this->doiClient;
        $doiCrossrefStatusCode = $doiClient->getDOIStatus($this->doi);
        return $doiCrossrefStatusCode;
    }

    public function getResponseContentFromCrossref() {
        $doiClient = $this->getDOIClient();
        $response = $doiClient->getDOIResponse($this->doi);

        return $response;
    }
}