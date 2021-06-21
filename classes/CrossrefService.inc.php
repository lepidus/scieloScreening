<?php
import ('plugins.generic.scieloScreening.classes.DOIService');

class CrossrefService extends DOIService {

    const CROSSREF_STATUS_DOI_INVALID_CODE = 400;
    const CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.crossrefInvalidErrorCode';
    
    function __construct($doi, $doiClient) {
        parent::__construct($doi, $doiClient);

        $crossrefResponseStatus = [
            self::CROSSREF_STATUS_DOI_INVALID_CODE => 
            self::CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY,
        ];

        $this->responseStatusMapping += $crossrefResponseStatus;
    }
}
