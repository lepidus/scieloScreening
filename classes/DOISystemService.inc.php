<?php

class DOISystemService extends DOIService {

    const DOI_ORG_STATUS_DOI_FOUND_CODE = 302;
    const DOI_ORG_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.crossrefRequirement';

    const DOI_ORG_STATUS_DOI_NOT_FOUND_CODE = 404;
    const DOI_ORG_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDOINotFoundErrorCode';

    const DOI_ORG_STATUS_DOI_NULL_CODE = 301;
    const DOI_ORG_STATUS_DOI_NULL_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDOINullErrorCode';
    
    function __construct($doi, $doiClient) {
        parent::__construct($doi, $doiClient);

        $doiOrgResponseStatus = [
            self::DOI_ORG_STATUS_DOI_FOUND_CODE => 
            self::DOI_ORG_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY,
            self::DOI_ORG_STATUS_DOI_NOT_FOUND_CODE => 
            self::DOI_ORG_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY,
            self::DOI_ORG_STATUS_DOI_NULL_CODE => 
            self::DOI_ORG_STATUS_DOI_NULL_MESSAGE_LOCALE_KEY,
        ];

        $this->responseStatusMapping += $doiOrgResponseStatus;
    }
}