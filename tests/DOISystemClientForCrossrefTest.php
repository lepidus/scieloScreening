<?php

use PHPUnit\Framework\TestCase;

final class DOISystemClientForDOIORGResponseTest extends TestCase
{
    public function testCode200WhenResponseFromCrossrefIsSuccess(): void
    {
        $crossrefClient = new DOISystemClientForCrossref();
        $httpResponseCode = "HTTP/1.1 200 Ok";

        $expectedValidationResult = 200;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode400WhenResponseFromCrossrefIsInvalid(): void
    {
        $crossrefClient = new DOISystemClientForCrossref();
        $httpResponseCode = "HTTP/1.1 400 Bad Request";

        $expectedValidationResult = 400;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode500WhenResponseFromCrossrefIsNotFound(): void
    {
        $crossrefClient = new DOISystemClientForCrossref();
        $httpResponseCode = "HTTP/1.1 404 Not Found";

        $expectedValidationResult = 404;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode500WhenResponseFromCrossrefIsInternalServerProblem(): void
    {
        $crossrefClient = new DOISystemClientForCrossref();
        $httpResponseCode = "HTTP/1.1 500 Internal Server Error";

        $expectedValidationResult = 500;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}
