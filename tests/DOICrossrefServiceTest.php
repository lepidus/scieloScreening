<?php

use PHPUnit\Framework\TestCase;
require "DOISystemClientForTests.inc.php";

final class DOICrossrefServiceTest extends TestCase
{
    private $server = 'Crossref.Org';
    private $serverUrl = 'https://api.crossref.org/works?filter=doi:';

    public function testIsInvalidWhenResultsOnAHTTP400FromCrossref(): void {
        $DOICrossrefService = new DOICrossrefService("10.1145/1998076.1998132", new DOISystemClientForTests($this->server, $this->serverUrl, 400));

        $expectedValidationResult = DOICrossrefService::CROSSREF_STATUS_DOI_INVALID_MESSAGE_LOCALE_KEY;
        
        $validationResult = $DOICrossrefService->getStatusResponseMessage()['key'];
        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}