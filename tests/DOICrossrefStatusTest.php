<?php

use PHPUnit\Framework\TestCase;
require "DOISystemClientForTests.inc.php";

final class CrossrefNonExistentDOITest extends TestCase
{
    public function testIsInvalidWhenResultsOnAHTTP400FromDOIOrg(): void {
        $doiCrossrefStatus = new DOICrossrefStatus("10.1145/1998076.1998132", new DOISystemClientforTests(400), 'Crossref.org');

        $expectedValidationResult = 400;
        $validationResult = $doiCrossrefStatus->getResponseFromCrossref();

        $this->assertEquals($expectedValidationResult, $validationResult);
    }
}