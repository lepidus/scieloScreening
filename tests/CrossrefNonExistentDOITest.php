<?php

import('lib.pkp.tests.PKPTestCase');
require "DOISystemClientForTests.inc.php";
import('plugins.generic.scieloScreening.classes.CrossrefNonExistentDOI');

class CrossrefNonExistentDOITest extends PKPTestCase
{
    private $crossrefNonExistentDOI;

    function setUp() : void {
        $this->crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132");
    }

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(302));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_DOI_FOUND_MESSAGE_LOCALE_KEY)
        ];

        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }


    public function testIsInvalidWhenResultsOnAHTTP500FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(500));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_INTERNAL_SERVER_ERROR_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(404));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_STATUS_DOI_NOT_FOUND_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOIOrg(): void
    {
        $httpStatus = 408;
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests($httpStatus));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::HTTPS_UNKNOWN_ERROR_CODE_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOIOrg(): void
    {
        $exceptionWithCommunication = true;
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(null, $exceptionWithCommunication));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => __(CrossrefNonExistentDOI::COMMUNICATION_FAILURE_MESSAGE_LOCALE_KEY)
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}