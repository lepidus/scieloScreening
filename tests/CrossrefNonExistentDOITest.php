<?php

use PHPUnit\Framework\TestCase;
require "DOISystemClientForTests.inc.php";

final class CrossrefNonExistentDOITest extends TestCase
{

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(302), 'DOI.org');

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY;

        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }


    public function testIsInvalidWhenResultsOnAHTTP500FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(500), 'DOI.org');

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(404), 'DOI.org');

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP301FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(301), 'DOI.org');

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NULL_ERROR_CODE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(408), 'DOI.org');

        $expectedValidationResult = CrossrefNonExistentDOI::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOIOrg(): void
    {
        $exceptionWithCommunication = true;
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(null, $exceptionWithCommunication), 'DOI.org');

        $expectedValidationResult =  CrossrefNonExistentDOI::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY;
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}