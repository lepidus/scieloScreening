<?php

namespace APP\plugins\generic\scieloScreening\classes;

use APP\plugins\generic\scieloScreening\classes\DOIService;

class CrossrefService extends DOIService
{
    public const CROSSREF_STATUS_DOI_INVALID_CODE = 400;
    public const CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.crossrefInvalidErrorCode';

    public function __construct($doi, $doiClient)
    {
        parent::__construct($doi, $doiClient);

        $crossrefResponseStatus = [
            self::CROSSREF_STATUS_DOI_INVALID_CODE =>
            self::CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY,
        ];

        $this->responseStatusMapping += $crossrefResponseStatus;
    }
}
