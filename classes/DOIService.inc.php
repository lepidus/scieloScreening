<?php

class DOIService
{
    protected $doiClient;
    protected $doi;
    protected $responseStatusMapping = [];

    public const HTTPS_STATUS_SUCCESS_CODE = 200;
    public const VALIDATION_ERROR_STATUS = 0;

    public const HTTPS_STATUS_INTERNAL_SERVER_ERROR_CODE = 500;
    public const HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpServerErrorCode';

    public const HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.unknownHttpErrorCode';
    public const COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.communicationFailure';

    public function __construct($doi, $doiClient)
    {
        $this->doi = $doi;
        $this->doiClient = $doiClient;

        $internalServerResponseStatus = [
            self::HTTPS_STATUS_INTERNAL_SERVER_ERROR_CODE => self::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY,
        ];

        $this->responseStatusMapping += $internalServerResponseStatus;
    }

    public function getParams()
    {
        $params = array(
            'server' => $this->doiClient->getServer(),
        );
        return $params;
    }

    public function getMessage($key, $params = array())
    {
        return array(
            'key' => $key,
            'params' => $params,
        );
    }

    public function getStatusResponseMessage()
    {
        try {
            $httpErrorCode = $this->doiClient->getDOIStatus($this->doi);

            if (array_key_exists($httpErrorCode, $this->responseStatusMapping)) {
                if ($httpErrorCode == self::HTTPS_STATUS_INTERNAL_SERVER_ERROR_CODE) {
                    return $this->getMessage($this->responseStatusMapping[$httpErrorCode], $this->getParams());
                }
                return $this->getMessage($this->responseStatusMapping[$httpErrorCode]);
            }
            return $this->getMessage(self::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY, $this->getParams());
        } catch (Exception $exception) {
            return $this->getMessage(self::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY, $this->getParams());
        }
    }

    public function getResponseStatusCode()
    {
        return $this->doiClient->getDOIStatus($this->doi);
        ;
    }

    public function getResponseContent()
    {
        return $this->doiClient->getDOIResponse($this->doi);
    }

    public function DOIExists()
    {
        $responseStatusCode = $this->getResponseStatusCode();
        return $responseStatusCode == self::HTTPS_STATUS_SUCCESS_CODE;
    }
}
