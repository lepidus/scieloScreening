<?php

namespace APP\plugins\generic\scieloScreening\classes;

use APP\plugins\generic\scieloScreening\classes\DOIService;

class DOISystemService extends DOIService
{
    public const DOI_ORG_STATUS_DOI_FOUND_CODE = 302;
    public const DOI_ORG_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.crossrefRequirement';

    public const DOI_ORG_STATUS_DOI_NOT_FOUND_CODE = 404;
    public const DOI_ORG_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDOINotFoundErrorCode';

    public const DOI_ORG_STATUS_DOI_NULL_CODE = 301;
    public const DOI_ORG_STATUS_DOI_NULL_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.httpDOINullErrorCode';

    public function __construct($doi, $doiClient)
    {
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
