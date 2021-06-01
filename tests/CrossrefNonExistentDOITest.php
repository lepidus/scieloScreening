<?php

import('lib.pkp.tests.PKPTestCase');
require "DOISystemClientForTests.inc.php";
import('plugins.generic.scieloScreening.classes.CrossrefNonExistentDOI');

class CrossrefNonExistentDOITest extends PKPTestCase
{

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(302));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY)
        ];

        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }


    public function testIsInvalidWhenResultsOnAHTTP500FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(500));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(404));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(408));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOIOrg(): void
    {
        $exceptionWithCommunication = true;
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForTests(null, $exceptionWithCommunication));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}