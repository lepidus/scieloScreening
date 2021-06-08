<?php

use PHPUnit\Framework\TestCase;

final class DOISystemClientForDOIORGResponseTest extends TestCase
{
    public function test302ResponseWhenHTTPStatusIsDOIFoundFromDOIOrg(): void
    {
        $doiSystemClientForDOIOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 302";
        
        $expectedValidationResult =  302;
        $validationResult =  $doiSystemClientForDOIOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test404ResponseWhenHTTPStatusIsNotFoundFromDOIOrg(): void
    {
        $doiSystemClientForDOIOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 404";
        
        $expectedValidationResult =  404;
        $validationResult =  $doiSystemClientForDOIOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test500ResponseWhenHTTPStatusIsInternalServerProblemFromDOIOrg(): void
    {
        $doiSystemClientForDOIOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 500";
        
        $expectedValidationResult =  500;
        $validationResult =  $doiSystemClientForDOIOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test301ResponseWhenHTTPStatusIsDOINullFromDOIOrg(): void
    {
        $doiSystemClientForDOIOrgResponse = new DOISystemClientForDOIORGResponse();
        $httpResponseCode = "HTTP/1.1 301";
        
        $expectedValidationResult =  301;
        $validationResult =  $doiSystemClientForDOIOrgResponse->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}