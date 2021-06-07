<?php

use PHPUnit\Framework\TestCase;

final class DOISystemClientForDOIORGResponseTest extends TestCase
{
    public function test302ResponseWhenHTTPStatusIsDOIFoundFromDoiOrg(): void
    {
        $doiSystemClientForDoiOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 302";
        
        $expectedValidationResult =  302;
        $validationResult =  $doiSystemClientForDoiOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test404ResponseWhenHTTPStatusIsNotFoundFromDoiOrg(): void
    {
        $doiSystemClientForDoiOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 404";
        
        $expectedValidationResult =  404;
        $validationResult =  $doiSystemClientForDoiOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test500ResponseWhenHTTPStatusIsInternalServerProblemFromDoiOrg(): void
    {
        $doiSystemClientForDoiOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 500";
        
        $expectedValidationResult =  500;
        $validationResult =  $doiSystemClientForDoiOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test301ResponseWhenHTTPStatusIsDOINullFromDoiOrg(): void
    {
        $doiSystemClientForDoiOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 301";
        
        $expectedValidationResult =  301;
        $validationResult =  $doiSystemClientForDoiOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}