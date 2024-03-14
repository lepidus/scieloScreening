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

        $affAuthors = ["UFAM", "USP"];
        $nameAuthors = ["Jhonathan Miranda", "Atila Iamarino"];
        list($statusAff, $authorsWithoutAff) = $checker->checkAffiliationAuthors($affAuthors, $nameAuthors);
        $this->assertTrue($statusAff);
        $this->assertEmpty($authorsWithoutAff);

        $affAuthors = [null, "USP"];
        list($statusAff, $authorsWithoutAff) = $checker->checkAffiliationAuthors($affAuthors, $nameAuthors);
        $this->assertFalse($statusAff);
        $this->assertEquals(["Jhonathan Miranda"], $authorsWithoutAff);

        $affAuthors = [null, ""];
        list($statusAff, $authorsWithoutAff) = $checker->checkAffiliationAuthors($affAuthors, $nameAuthors);
        $this->assertFalse($statusAff);
        $this->assertEquals($nameAuthors, $authorsWithoutAff);
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
