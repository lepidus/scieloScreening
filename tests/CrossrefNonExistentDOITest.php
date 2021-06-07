<?php

use PHPUnit\Framework\TestCase;
require "DOISystemClientForTests.inc.php";

final class CrossrefNonExistentDOITest extends TestCase
{

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 302"));

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY;

        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }


    public function testIsInvalidWhenResultsOnAHTTP500FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 500"));

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 404"));

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP301FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 301"));

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NULL_ERROR_CODE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 408"));

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOIOrg(): void
    {
        $exceptionWithCommunication = true;
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(null, $exceptionWithCommunication));

        $expectedValidationResult =  CrossrefNonExistentDOI::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testGetHttpErrorCodeFromDOIOrgResponse(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests("HTTP/1.1 404"));
        $doi = $crossrefNonExistentDOI->getDoi();
        $doiClient = $crossrefNonExistentDOI->getDoiClient();
        $httpStatusResponseByDoiClient = $doiClient->getDoiStatus($doi);

        $expectedValidationResult =  404;
        $validationResult = $crossrefNonExistentDOI->getDoiClient()->getHTTPErrorCodeByHTTPStatus($httpStatusResponseByDoiClient);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}