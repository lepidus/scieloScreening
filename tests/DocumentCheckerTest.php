<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloScreening\classes\DocumentChecker;

class DocumentCheckerTest extends TestCase
{
    private $dummyDocumentPath;
    private $orcidsDocumentPath;

    public function setUp(): void
    {
        $this->dummyDocumentPath = __DIR__ . "/assets/dummy_document.pdf";
        $this->orcidsDocumentPath = __DIR__ . "/assets/orcids_document.pdf";
    }

    public function testDocumentWithoutOrcids()
    {
        $documentChecker = new DocumentChecker($this->dummyDocumentPath);
        $expectedOrcids = [];

        $this->assertEquals($expectedOrcids, $documentChecker->checkTextOrcids());
    }

    public function testDocumentWithOrcids()
    {
        $documentChecker = new DocumentChecker($this->orcidsDocumentPath);
        $expectedOrcids = [
            '0000-0001-5727-2427',
            '0000-0002-1648-966x',
            '0000-0002-1825-0097',
            '0000-0003-1984-8629'
        ];

        $this->assertEquals($expectedOrcids, $documentChecker->checkTextOrcids());
    }
    public function testExtractionDoesNotTouchSiblingText(): void
    {
        $directory = sys_get_temp_dir() . '/scielo-pdf-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $pdf = $directory . '/document.pdf';
        $text = $directory . '/document.txt';
        copy($this->orcidsDocumentPath, $pdf);
        file_put_contents($text, 'must remain intact');
        try {
            $checker = new DocumentChecker($pdf);
            $this->assertNotEmpty($checker->checkTextOrcids());
            $this->assertFileExists($text);
            $this->assertSame('must remain intact', file_get_contents($text));
        } finally {
            @unlink($text);
            unlink($pdf);
            rmdir($directory);
        }
    }

    public function testOversizedInputIsNotExtracted(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'scielo-large-');
        $handle = fopen($file, 'w');
        ftruncate($handle, 20 * 1024 * 1024 + 1);
        fclose($handle);
        try {
            $checker = new DocumentChecker($file);
            $this->assertFalse($checker->extractionSucceeded());
            $this->assertSame([], $checker->checkTextOrcids());
        } finally {
            unlink($file);
        }
    }
}
