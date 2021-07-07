<?php

use PHPUnit\Framework\TestCase;

final class DOISystemServiceTest extends TestCase
{
    private $server = 'DOI.Org';
    private $serverUrl = 'https://doi.org/';

    public function testIsInvalidWhenResultsOnAHTTP302FromDOISystem(): void {
        $DOISystemService = new DOISystemService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 302));

        $expectedValidationResult = DOISystemService::DOI_ORG_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOISystemService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOISystem(): void
    {
        $DOISystemService = new DOISystemService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 404));

        $expectedValidationResult = DOISystemService::DOI_ORG_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOISystemService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP301FromDOISystem(): void
    {
        $DOISystemService = new DOISystemService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 301));

        $expectedValidationResult = DOISystemService::DOI_ORG_STATUS_DOI_NULL_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOISystemService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}