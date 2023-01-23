<?php

use PHPUnit\Framework\TestCase;

require "DOISystemClientForTests.inc.php";

final class CrossrefServiceTest extends TestCase
{
    private $server = 'Crossref.Org';
    private $serverUrl = 'https://api.crossref.org/works?filter=doi:';

    public function testIsInvalidWhenResultsOnAHTTP400FromCrossref(): void
    {
        $crossrefService = new CrossrefService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 400));

        $expectedValidationResult = CrossrefService::CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY;

        $validationResult = $crossrefService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}
