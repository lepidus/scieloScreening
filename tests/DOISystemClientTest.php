<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloScreening\classes\DOISystemClient;

final class DOISystemClientTest extends TestCase
{
    private $server = 'DOI.Org';
    private $serverUrl = 'https://doi.org/';

    public function test302ResponseWhenHttpStatusIsDoiFoundFromDoiSystem(): void
    {
        $doiSystemClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 302";

        $expectedValidationResult =  302;
        $validationResult =  $doiSystemClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test404ResponseWhenHttpStatusIsNotFoundFromDoiSystem(): void
    {
        $doiSystemClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 404";

        $expectedValidationResult =  404;
        $validationResult =  $doiSystemClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test500ResponseWhenHttpStatusIsInternalServerProblemFromDoiSystem(): void
    {
        $doiSystemClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 500";

        $expectedValidationResult =  500;
        $validationResult =  $doiSystemClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function test301ResponseWhenHttpStatusIsDoiNullFromDoiSystem(): void
    {
        $doiSystemClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 301";

        $expectedValidationResult =  301;
        $validationResult =  $doiSystemClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode200WhenResponseFromCrossrefIsSuccess(): void
    {
        $crossrefClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 200 Ok";

        $expectedValidationResult = 200;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode400WhenResponseFromCrossrefIsInvalid(): void
    {
        $crossrefClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 400 Bad Request";

        $expectedValidationResult = 400;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testCode500WhenResponseFromCrossrefIsInternalServerProblem(): void
    {
        $crossrefClient = new DOISystemClient($this->server, $this->serverUrl);
        $httpResponseCode = "HTTP/1.1 500 Internal Server Error";

        $expectedValidationResult = 500;
        $validationResult = $crossrefClient->getHTTPErrorCodeByHTTPStatus($httpResponseCode);

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}
