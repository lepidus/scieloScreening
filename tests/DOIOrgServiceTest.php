<?php

use PHPUnit\Framework\TestCase;
import ('plugins.generic.scieloScreening.tests.DOISystemClientForTests');
import ('plugins.generic.scieloScreening.classes.DOIOrgService');

final class DOIOrgServiceTest extends TestCase
{
    private $server = 'DOI.Org';
    private $serverUrl = 'https://doi.org/';

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void {
        $DOIOrgService = new DOIOrgService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 302));

        $expectedValidationResult = DOIOrgService::DOI_ORG_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIOrgService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $DOIOrgService = new DOIOrgService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 404));

        $expectedValidationResult = DOIOrgService::DOI_ORG_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIOrgService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP301FromDOIOrg(): void
    {
        $DOIOrgService = new DOIOrgService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 301));

        $expectedValidationResult = DOIOrgService::DOI_ORG_STATUS_DOI_NULL_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIOrgService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}