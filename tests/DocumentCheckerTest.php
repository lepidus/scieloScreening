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

    public function testExtractionDoesNotTouchTheSourceOrSiblingTextFiles(): void
    {
        $directory = sys_get_temp_dir() . '/scielo-pdf-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $pdf = $directory . '/document.pdf';
        $siblingText = $directory . '/document.txt';
        $galleyStoredAsText = $directory . '/galley.txt';
        copy($this->orcidsDocumentPath, $pdf);
        file_put_contents($siblingText, 'must remain intact');
        file_put_contents($galleyStoredAsText, 'galley stored with a txt extension');
        try {
            $checker = new DocumentChecker($pdf);
            $this->assertNotEmpty($checker->checkTextOrcids());
            $this->assertSame('must remain intact', file_get_contents($siblingText));

            $checker = new DocumentChecker($galleyStoredAsText);
            $this->assertSame([], $checker->checkTextOrcids());
            $this->assertSame('galley stored with a txt extension', file_get_contents($galleyStoredAsText));
        } finally {
            foreach ([$pdf, $siblingText, $galleyStoredAsText] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            rmdir($directory);
        }
    }
}
