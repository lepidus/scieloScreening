<?php

use PHPUnit\Framework\TestCase;

final class DOISystemClientForDOIORGResponseTest extends TestCase
{
    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132", new DOISystemClientForDOIORGResponse());
        $doi = $crossrefNonExistentDOI->getDoi();
        $doiClient = $crossrefNonExistentDOI->getDoiClient();
        
        $expectedValidationResult =  302;
        $validationResult =  $doiClient->getDoiStatus($doi);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("1110290", new DOISystemClientForDOIORGResponse());
        $doi = $crossrefNonExistentDOI->getDoi();
        $doiClient = $crossrefNonExistentDOI->getDoiClient();
        
        $expectedValidationResult =  404;
        $validationResult =  $doiClient->getDoiStatus($doi);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP301FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("", new DOISystemClientForDOIORGResponse());
        $doi = $crossrefNonExistentDOI->getDoi();
        $doiClient = $crossrefNonExistentDOI->getDoiClient();
        
        $expectedValidationResult =  301;
        $validationResult =  $doiClient->getDoiStatus($doi);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP400FromDOIOrg(): void
    {
        $crossrefNonExistentDOI = new CrossrefNonExistentDOI("%%%%%%@@@##$$%%", new DOISystemClientForDOIORGResponse());
        $doi = $crossrefNonExistentDOI->getDoi();
        $doiClient = $crossrefNonExistentDOI->getDoiClient();
        
        $expectedValidationResult =  400;
        $validationResult =  $doiClient->getDoiStatus($doi);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}