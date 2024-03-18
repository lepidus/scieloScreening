<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;

final class ScreeningCheckerTest extends TestCase
{
    public function testNameUppercase(): void
    {
        $checker = new ScreeningChecker();

        $normalName = "Carlos Magno";
        $this->assertFalse($checker->isUppercase($normalName));

        $uppercaseName = "ATILA IAMARINO";
        $this->assertTrue($checker->isUppercase($uppercaseName));
    }

    public function testHasUppercaseAuthor(): void
    {
        $checker = new ScreeningChecker();

        $authorsOkay = ["Alan Turing", "Ada Lovelace", "Nikola Tesla"];
        $this->assertFalse($checker->checkHasUppercaseAuthors($authorsOkay));

        $authorsNotOkay = ["ALAN TURING", "Nikola Tesla", "Universidade do Amazonas (UA)"];
        $this->assertTrue($checker->checkHasUppercaseAuthors($authorsNotOkay));
    }

    public function testOrcidAuthors(): void
    {
        $checker = new ScreeningChecker();

        $orcids = [null, "https://orcid.org/0000-0002-1825-0097", null];
        $this->assertTrue($checker->checkOrcidAuthors($orcids));

        $orcids = [null, null, ""];
        $this->assertFalse($checker->checkOrcidAuthors($orcids));
    }

    public function testAffiliationAuthors(): void
    {
        $checker = new ScreeningChecker();

        $authorsAffiliations = ["UFAM", "USP"];
        $nameAuthors = ["Jhonathan Miranda", "Atila Iamarino"];
        list($statusAffiliation, $authorsWithoutAffiliation) = $checker->checkAffiliationAuthors($authorsAffiliations, $nameAuthors);
        $this->assertTrue($statusAffiliation);
        $this->assertEmpty($authorsWithoutAffiliation);

        $authorsAffiliations = [null, "USP"];
        list($statusAffiliation, $authorsWithoutAffiliation) = $checker->checkAffiliationAuthors($authorsAffiliations, $nameAuthors);
        $this->assertFalse($statusAffiliation);
        $this->assertEquals(["Jhonathan Miranda"], $authorsWithoutAffiliation);

        $authorsAffiliations = [null, ""];
        list($statusAffiliation, $authorsWithoutAffiliation) = $checker->checkAffiliationAuthors($authorsAffiliations, $nameAuthors);
        $this->assertFalse($statusAffiliation);
        $this->assertEquals($nameAuthors, $authorsWithoutAffiliation);
    }

    public function testNumberPdfs(): void
    {
        $checker = new ScreeningChecker();

        $labelsGalleys = ["application/pdf", "image/jpeg"];
        list($status, $number) = $checker->checkNumberPdfs($labelsGalleys);
        $this->assertTrue($status);
        $this->assertEquals(1, $number);

        $labelsGalleys = ["application/pdf", "application/pdf", "application/pdf"];
        list($status, $number) = $checker->checkNumberPdfs($labelsGalleys);
        $this->assertFalse($status);
        $this->assertEquals(3, $number);
    }
}
