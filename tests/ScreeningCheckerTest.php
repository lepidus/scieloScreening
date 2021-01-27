<?php

use PHPUnit\Framework\TestCase;

final class ScreeningCheckerTest extends TestCase {
    public function testNameUppercase() : void {
        $checker = new ScreeningChecker();
        
        $normalName = "Carlos Magno";
        $this->assertFalse($checker->isUppercase($normalName));
        
        $uppercaseName = "ATILA IAMARINO";
        $this->assertTrue($checker->isUppercase($uppercaseName));
    }

    public function testHasUppercaseAuthor() : void {
        $checker = new ScreeningChecker();

        $authorsOkay = ["Alan Turing", "Ada Lovelace", "Nikola Tesla"];
        $this->assertFalse($checker->checkHasUppercaseAuthors($authorsOkay));

        $authorsNotOkay = ["ALAN TURING", "Nikola Tesla", "Universidade do Amazonas (UA)"];
        $this->assertTrue($checker->checkHasUppercaseAuthors($authorsNotOkay));
    }

    public function testOrcidAuthors() : void {
        $checker = new ScreeningChecker();

        $orcids = ["", "https://orcid.org/0000-0002-1825-0097", ""];
        $this->assertTrue($checker->checkOrcidAuthors($orcids));

        $orcids = ["", "", ""];
        $this->assertFalse($checker->checkOrcidAuthors($orcids));
    }

    public function testAffiliationAuthors() : void {
        $checker = new ScreeningChecker();

        $affAuthors = ["UFAM", "USP"];
        $nameAuthors = ["Jhonathan Miranda", "Atila Iamarino"];
        $this->assertTrue($checker->checkAffiliationAuthors($affAuthors, $nameAuthors)[0]);

        $affAuthors = ["", "USP"];
        list($statusAff, $authorsNoAff) = $checker->checkAffiliationAuthors($affAuthors, $nameAuthors);
        $this->assertFalse($statusAff);
        $this->assertEquals("Jhonathan Miranda", $authorsNoAff[0]);
    }

    public function testNumberPdfs() : void {
        $checker = new ScreeningChecker();

        $labelsGalleys = ["pdf", "jpg",];
        list($status, $number) = $checker->checkNumberPdfs($labelsGalleys);
        $this->assertTrue($status);
        $this->assertEquals(1, $number);

        $labelsGalleys = ["pdf", "pdf", "pdf"];
        list($status, $number) = $checker->checkNumberPdfs($labelsGalleys);
        $this->assertFalse($status);
        $this->assertEquals(3, $number);
    }

    public function testCheckDoiCrossref() : void {
        $checker = new ScreeningChecker();
        $responseCrossref = $checker->getFromCrossref("10.6666/abcde");

        $this->assertFalse($checker->checkDoiCrossref($responseCrossref));
    }

    public function testCheckDoiFromAuthor() : void {
        $checker = new ScreeningChecker();
        $responseCrossref = $checker->getFromCrossref("10.1016/j.datak.2003.10.003");
        
        $authorsCrossref = $responseCrossref['message']['items'][0]['author'];
        $authorSubmission = "Jhonathan de Seixas Miranda";

        $this->assertFalse($checker->checkDoiFromAuthor($authorSubmission, $authorsCrossref));
    }

    public function testCheckDoiArticle() : void {
        $checker = new ScreeningChecker();
        $responseCrossref = $checker->getFromCrossref("10.1145/1998076.1998132");
        
        $itemCrossref = $responseCrossref['message']['items'][0];

        $this->assertFalse($checker->checkDoiArticle($itemCrossref));
    }

    public function testCheckDoiRepeated() : void {
        $checker = new ScreeningChecker();
        $dois = ["10.1016/j.datak.2003.10.003", "10.1016/j.datak.2003.10.003", "10.1145/1998076.1998132"];

        $this->assertTrue($checker->checkDoiRepeated($dois));
    }

    public function testCheckDoisLastTwoYears() : void {
        $checker = new ScreeningChecker();
        
        $responseCrossref = $checker->getFromCrossref("10.1145/1998076.1998132");
        $firstYear = $responseCrossref['message']['items'][0]['published-print']['date-parts'][0][0];
        $responseCrossref = $checker->getFromCrossref("10.1016/j.datak.2003.10.003");
        $secondYear = $responseCrossref['message']['items'][0]['published-print']['date-parts'][0][0];

        $this->assertFalse($checker->checkDoisLastTwoYears([$firstYear, $secondYear]));
    }

    public function testValidateDOI() : void {
        $checker = new ScreeningChecker();

        $dois = ["10.1016/j.datak.2003.10.003", "10.1016/S0169-023X(01)00047-7"];
        $nameAuthor = "Altigran S.da Silva";

        $firstResponse = $checker->getFromCrossref($dois[0]);
        $this->assertTrue($checker->checkDoiCrossref($firstResponse));

        $secondResponse = $checker->getFromCrossref($dois[1]);
        $this->assertTrue($checker->checkDoiCrossref($secondResponse));
        
        $firstItem = $firstResponse['message']['items'][0];
        $secondItem = $secondResponse['message']['items'][0];

        $this->assertTrue($checker->checkDoiFromAuthor($nameAuthor, $firstItem['author']));
        $this->assertTrue($checker->checkDoiFromAuthor($nameAuthor, $secondItem['author']));

        $this->assertTrue($checker->checkDoiArticle($firstItem));
        $this->assertTrue($checker->checkDoiArticle($secondItem));
    }
}