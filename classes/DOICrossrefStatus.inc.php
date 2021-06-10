<?php

class DOICrossrefStatus extends CrossrefNonExistentDOI {

    const CROSSREF_STATUS_SUCCESS_CODE = 200;

    const CROSSREF_STATUS_DOI_INVALID_CODE = 400;
    const CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY = 'plugins.generic.scieloScreening.doiCrossrefInvalidErrorCode';

    const CROSSREF_SERVER = 'Crossref.org';
    const CROSSREF_BASE_URL = 'https://api.crossref.org/works?filter=doi:';

    public function getResponseFromCrossref(){
        $doiClient = $this->getDoiClient();
        $crossrefResponse = $doiClient->getDOIStatus(self::CROSSREF_BASE_URL, $this->getDoi());

        return $crossrefResponse;
    }

}