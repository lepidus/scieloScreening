<?php

use PHPUnit\Framework\TestCase;

final class DOIServiceTest extends TestCase
{
    private $server = 'DOI.Org';
    private $serverUrl = 'https://doi.org/';

    public function testIsInvalidWhenResultsOnAHTTP400FromDOISystem(): void {
        $DOIService = new DOIService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 500));

        $expectedValidationResult = DOIService::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOISystem(): void
    {
        $DOIService = new DOIService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 408));

        $expectedValidationResult = DOIService::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOISystem(): void
    {
        $exceptionWithCommunication = true;
        $DOIService = new DOIService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, null, $exceptionWithCommunication), 'DOI.org');

        $expectedValidationResult =  DOIService::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOIService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIfDOIExistsInCrossref(): void
    {
        $DOIService = new DOIService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 200), 'DOI.org');
        
        $validationResult = $DOIService->DOIExists();
        $this->assertTrue($validationResult);
    }
}