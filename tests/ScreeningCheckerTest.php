<?php

use PHPUnit\Framework\TestCase;

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

        $orcids = ["", "https://orcid.org/0000-0002-1825-0097", ""];
        $this->assertTrue($checker->checkOrcidAuthors($orcids));

        $orcids = ["", "", ""];
        $this->assertFalse($checker->checkOrcidAuthors($orcids));
    }

    public function testAffiliationAuthors(): void
    {
        $checker = new ScreeningChecker();

        $affAuthors = ["UFAM", "USP"];
        $nameAuthors = ["Jhonathan Miranda", "Atila Iamarino"];
        $this->assertTrue($checker->checkAffiliationAuthors($affAuthors, $nameAuthors)[0]);

        $affAuthors = ["", "USP"];
        list($statusAff, $authorsNoAff) = $checker->checkAffiliationAuthors($affAuthors, $nameAuthors);
        $this->assertFalse($statusAff);
        $this->assertEquals("Jhonathan Miranda", $authorsNoAff[0]);
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

    public function testDOIAuthorNameEqualsSubmissionAuthorName(): void
    {
        $checker = new ScreeningChecker();

        $authorsCrossrefCase1 =  array(array('given' => "Síntique", 'family' => "Priscila Alves Lopes"));
        $authorsCrossrefCase2 =  array(array('given' => "Maria", 'family' => "Síntique Lopes"));
        $authorsCrossrefCase3 =  array(array('given' => "Yves", 'family' => "Schafer Weiss"));

        $authorSubmissionCase1 = "Síntique Priscila Alves Lopes";
        $authorSubmissionCase2 = "Yves Schäfer Weiß";

        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmissionCase1, $authorsCrossrefCase1));
        $this->assertFalse($checker->checkDOIFromAuthor($authorSubmissionCase1, $authorsCrossrefCase2));
        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmissionCase2, $authorsCrossrefCase3));
    }

    public function testDOIAuthorNameWithAbreviationEqualsSubmissionAuthorName(): void
    {
        $checker = new ScreeningChecker();

        $authorsCrossrefCase1 =  array(array('given' => "Síntique", 'family' => "Priscila A. Lopes"));
        $authorsCrossrefCase2 =  array(array('given' => "Síntique", 'family' => "P. Alves Lopes"));
        $authorsCrossrefCase3 =  array(array('given' => "Síntique", 'family' => "P. A. Lopes"));
        $authorsCrossrefCase4 =  array(array('given' => "Síntique", 'family' => "X. Z. Lopes"));

        $authorSubmission = "Síntique Priscila Alves Lopes";

        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossrefCase1));
        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossrefCase2));
        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossrefCase3));
        $this->assertFalse($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossrefCase4));
    }

    public function testDOIAuthorNameOnlyFirstAndLastNameEqualsSubmissionAuthorName(): void
    {
        $checker = new ScreeningChecker();

        $authorsCrossref = array(
            array(
                    "given" => "Jamile",
                    "family" => "Xavier",
                    "sequence" => "first",
                    "affiliation" => array()
            ),
            array(
                    'given' => "Síntique",
                    "family" => "Lopes",
                    "sequence" => "additional",
                    "affiliation" => array()
            )
        );

        $authorSubmission = "Síntique Priscila Alves Lopes";

        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossref));
    }

    public function testDOIAuthorNameWithMiddleNameDifferentSubmissionAuthorName(): void
    {
        $checker = new ScreeningChecker();

        $authorsCrossref =  array(array('given' => "Síntique", 'family' => "das Dores Lopes"));

        $authorSubmission = "Síntique Priscila Alves Lopes";

        $this->assertFalse($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossref));
    }

    public function testRemoveAccentuation(): void
    {
        $checker = new ScreeningChecker();
        $nameResultedCase1 = $checker->removeAccentuation("Síntique Priscila Alves Lopes");
        $nameResultedCase2 = $checker->removeAccentuation("Yves Müller Schröder");

        $nameExpectedCase1 = "Sintique Priscila Alves Lopes";
        $nameExpectedCase2 = "Yves Muller Schroder";

        $this->assertEquals($nameResultedCase1, $nameExpectedCase1);
        $this->assertEquals($nameResultedCase2, $nameExpectedCase2);
    }

    public function testDOIAuthorNameWithoutAccentuationEqualsSubmissionAuthorNameWithAccentuation(): void
    {
        $checker = new ScreeningChecker();

        $authorsCrossref =  array(array('given' => "Sintique", 'family' => "Lopes"));

        $authorSubmission = "Síntique Priscila Alves Lopes";

        $this->assertTrue($checker->checkDOIFromAuthor($authorSubmission, $authorsCrossref));
    }

    public function testCheckDOIArticle(): void
    {
        $checker = new ScreeningChecker();

        $type = array(
            'type' => 'proceedings-article'
        );

        $this->assertFalse($checker->checkDOIArticle($type));
    }

    public function testCheckDOIRepeated(): void
    {
        $checker = new ScreeningChecker();
        $dois = ["10.1016/j.datak.2003.10.003", "10.1016/j.datak.2003.10.003", "10.1145/1998076.1998132"];

        $this->assertTrue($checker->checkDOIRepeated($dois));
    }

    public function testCheckDOIsLastTwoYears(): void
    {
        $checker = new ScreeningChecker();
        $firstYear = 2011;
        $secondYear = 2004;

        $this->assertFalse($checker->checkDOIsLastTwoYears([$firstYear, $secondYear]));
    }

    public function testValidateDOI(): void
    {
        $checker = new ScreeningChecker();

        $nameAuthor = "Altigran S. da Silva";

        $firstResponse = array(
            'status' => 'ok',
            'message' => array(
                'items' => array(
                    'author' => array(
                        array(
                            'given' => 'Altigran S.',
                            'family' => 'da Silva',
                            'sequence' => 'first',
                            'affiliation' => array()
                        ),
                        array(
                            'given' => 'Marcos André',
                            'family' => 'Gonçalves',
                            'sequence' => 'additional',
                            'affiliation' => array()
                        )
                    ),
                    'type' => 'journal-article',
                )
            )
        );

        $secondResponse = array(
            'status' => 'ok',
            'message'=> array(
                'items' => array(
                    'author' => array(
                        array(
                            'given' => 'Alberto H.F.',
                            'family' => 'Laender',
                            'sequence' => 'first',
                            'affiliation' => array()
                        ),
                        array(
                            'given' => 'Altigran S.',
                            'family' => 'da Silva',
                            'sequence' => 'additional',
                            'affiliation' => array()
                        )
                    ),
                    'type' => 'journal-article',
                )
            )
        );

        $this->assertTrue($checker->checkCrossrefResponse($firstResponse));
        $this->assertTrue($checker->checkCrossrefResponse($secondResponse));

        $firstItem = $firstResponse['message']['items'];
        $secondItem = $secondResponse['message']['items'];

        $this->assertTrue($checker->checkDOIArticle($firstItem));
        $this->assertTrue($checker->checkDOIArticle($secondItem));

        $firstItemAuthors = $firstItem['author'];
        $secondItemAuthors = $secondItem['author'];

        $this->assertTrue($checker->checkDOIFromAuthor($nameAuthor, $firstItemAuthors));
        $this->assertTrue($checker->checkDOIFromAuthor($nameAuthor, $firstItemAuthors));
    }
}
