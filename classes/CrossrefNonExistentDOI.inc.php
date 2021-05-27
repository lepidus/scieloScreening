<?php

class CrossrefNonExistentDOI {

    private $doiClient;
    private $doi;

    const HTTPS_STATUS_DOI_FOUND = 302;
    const HTTPS_STATUS_DOI_NOT_FOUND = 404;
    const HTTPS_STATUS_INTERNAL_SERVER_ERROR = 500;
    const VALIDATION_ERROR_STATUS = 0;

    function __construct($doi) {
        $this->doi = $doi;
    }

    function setClient($doiClient) {
        $this->doiClient = $doiClient;
    }

    function getErrorMessage() {
        try {
            $httpStatusFromDOI = $this->doiClient->getDOIStatus($this->doi);
    
            $errorMapping = [
                self::HTTPS_STATUS_DOI_FOUND => "Apenas DOIs da Crossref são aceitos",
                self::HTTPS_STATUS_DOI_NOT_FOUND => "O DOI inserido não está registrado. Confirme se o mesmo está correto e, em caso de dúvida, verifique com a publicação de origem.",
                self::HTTPS_STATUS_INTERNAL_SERVER_ERROR => "Erro no servidor DOI.org",
            ];
    
            if (array_key_exists($httpStatusFromDOI, $errorMapping)) {
                return $this->getResponse($errorMapping[$httpStatusFromDOI]);
            }
            return $this->getResponse("Código de retorno" . $httpStatusFromDOI . "desconhecido de DOI.org. Tente novamente em alguns instantes e, caso o problema persista, por favor avise");
        }
        catch (Exception $exception) {
            return $this->getResponse("Falha na comunicação com DOI.org. Tente novamente em alguns instantes e, caso o problema persista, por favor avise");
        }
    }

    private function getResponse($errorMessage) {
        return   [
            'statusValidate' => self::VALIDATION_ERROR_STATUS,
            'messageError' => $errorMessage
        ];
    }
}