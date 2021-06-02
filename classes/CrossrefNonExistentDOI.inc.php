<?php

class CrossrefNonExistentDOI {

    private $doiClient;
    private $doi;

    const HTTPS_STATUS_DOI_FOUND = 302;
    const HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.doiCrossrefRequirement';

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

    function getHTTPErrorCodeByStatus ($httpStatusFromDOI) {
        if (str_contains($httpStatusFromDOI, self::HTTPS_STATUS_DOI_FOUND)) {
           return self::HTTPS_STATUS_DOI_FOUND;    
        }
        elseif (str_contains($httpStatusFromDOI, self::HTTPS_STATUS_DOI_NOT_FOUND)) {
            return self::HTTPS_STATUS_DOI_NOT_FOUND;
        }
        elseif (str_contains($httpStatusFromDOI, self::HTTPS_STATUS_INTERNAL_SERVER_ERROR)) {
            return self::HTTPS_STATUS_INTERNAL_SERVER_ERROR;
        }
        return self::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY;
    }

    function getErrorMessage() {
        try {
            if ($this->doiClient) {
                $httpStatusFromDOI = $this->doiClient->getDOIStatus($this->doi);
            }
            else {
                $httpStatusFromDOI = get_headers(self::DOI_ORG_BASE_URL . $this->doi)[0];
            }
            
            $httpErrorCode = $this->getHTTPErrorCodeByStatus($httpStatusFromDOI);
    
            $errorMapping = [
                self::HTTPS_STATUS_DOI_FOUND => __(self::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY),
                self::HTTPS_STATUS_DOI_NOT_FOUND => __(self::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY),
                self::HTTPS_STATUS_INTERNAL_SERVER_ERROR => __(self::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY),
            ];
    
            if (array_key_exists($httpErrorCode, $errorMapping)) {
                return $this->getResponse($errorMapping[$httpErrorCode]);
            }
            return $this->getResponse(__(self::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY));
        }
        catch (Exception $exception) {
            return $this->getResponse(__(self::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY));
        }
    }

    private function getResponse($errorMessage) {
        return [
            'statusValidate' => self::VALIDATION_ERROR_STATUS,
            'messageError' => $errorMessage
        ];
    }
}