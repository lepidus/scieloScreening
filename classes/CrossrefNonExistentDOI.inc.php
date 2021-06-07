<?php

class CrossrefNonExistentDOI {

    private $doiClient;
    private $doi;

    const HTTPS_STATUS_DOI_FOUND = 302;
    const HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.doiCrossrefRequirement';

    const HTTPS_STATUS_DOI_NULL = 301;
    const HTTPS_STATUS_DOI_NULL_ERROR_CODE_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDoiNullErrorCode';

    const HTTPS_STATUS_DOI_NOT_FOUND = 404;
    const HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDOINotFoundErrorCode';

    const HTTPS_STATUS_INTERNAL_SERVER_ERROR = 500;
    const HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpServerErrorCode';

    const HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.unknownHttpErrorCode';

    const COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.communicationFailure';

    const DOI_ORG_BASE_URL = "https://doi.org/";

    const VALIDATION_ERROR_STATUS = 0;

    function __construct($doi, $doiClient = null) {
        $this->doi = $doi;
        $this->doiClient = $doiClient;
    }

    public function getDoi() {
        return $this->doi;
    }

    public function getDoiClient() {
        return $this->doiClient;
    }

    function getErrorMessage() {
        try {       
            $doiClient = $this->getDoiClient();
            $doiStatus = $doiClient->getDoiStatus($this->getDoi());   
            $httpErrorCode = $doiClient->getHTTPErrorCodeByHTTPStatus($doiStatus);
    
            $errorMapping = [
                self::HTTPS_STATUS_DOI_FOUND => self::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY,
                self::HTTPS_STATUS_DOI_NULL => self::HTTPS_STATUS_DOI_NULL_ERROR_CODE_MESSAGE_LOCALE_KEY,
                self::HTTPS_STATUS_DOI_NOT_FOUND => self::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY,
                self::HTTPS_STATUS_INTERNAL_SERVER_ERROR => self::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY,
            ];
    
            if (array_key_exists($httpErrorCode, $errorMapping)) {
                return $errorMapping[$httpErrorCode];
            }
            return self::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY;
        }
        catch (Exception $exception) {
            return self::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY;
        }
    }
}