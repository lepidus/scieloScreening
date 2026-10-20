<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\scieloScreening\classes\DocumentChecker;

class DocumentCheckerTest extends TestCase
{
    private $dummyDocumentPath;
    private $orcidsDocumentPath;
    private $workingDirectory;

    public function setUp(): void
    {
        $this->workingDirectory = sys_get_temp_dir() . '/scielo-checker-' . bin2hex(random_bytes(6));
        $this->dummyDocumentPath = __DIR__ . "/assets/dummy_document.pdf";
        $this->orcidsDocumentPath = __DIR__ . "/assets/orcids_document.pdf";
    }

    protected function tearDown(): void
    {
        foreach (glob($this->workingDirectory . '/*') as $file) {
            unlink($file);
        }
        if (is_dir($this->workingDirectory)) {
            rmdir($this->workingDirectory);
        }
    }

    public function testPublicLockCannotBlockPrivateExtraction(): void
    {
        $lock = fopen(sys_get_temp_dir() . '/scielo-screening-' . hash('sha256', dirname(__DIR__) . '/classes') . '.lock', 'c');
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        try {
            $checker = new DocumentChecker($this->orcidsDocumentPath, $this->workingDirectory);
            $this->assertTrue($checker->extractionSucceeded());
            $this->assertSame(0700, fileperms($this->workingDirectory) & 0777);
        } finally {
            fclose($lock);
        }
    }

    public function testDocumentWithoutOrcids()
    {
        $documentChecker = new DocumentChecker($this->dummyDocumentPath, $this->workingDirectory);
        $expectedOrcids = [];

        $this->assertEquals($expectedOrcids, $documentChecker->checkTextOrcids());
    }

    public function testDocumentWithOrcids()
    {
        $documentChecker = new DocumentChecker($this->orcidsDocumentPath, $this->workingDirectory);
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
            $checker = new DocumentChecker($pdf, $this->workingDirectory);
            $this->assertNotEmpty($checker->checkTextOrcids());
            $this->assertFileExists($text);
            $this->assertSame('must remain intact', file_get_contents($text));
        } finally {
            @unlink($text);
            unlink($pdf);
            rmdir($directory);
        }
    }

    public function testLargeOrcidListsRetainOnlyBudgetAndOverflowMarker(): void
    {
        $orcids = [];
        for ($i = 0; $i < 100; $i++) {
            $digits = str_pad((string) $i, 15, '0', STR_PAD_LEFT);
            $total = 0;
            foreach (str_split($digits) as $digit) {
                $total = ($total + (int) $digit) * 2;
            }
            $checksum = (12 - $total % 11) % 11;
            $orcids[] = implode('-', str_split($digits . ($checksum === 10 ? 'X' : $checksum), 4));
        }
        $checker = (new ReflectionClass(DocumentChecker::class))->newInstanceWithoutConstructor();
        $text = new ReflectionProperty(DocumentChecker::class, 'fileText');
        $text->setValue($checker, 'orcid.org/0000-0000-0000-0000 ' . implode(' ', array_map(fn ($orcid) => 'orcid.org/' . $orcid . ' orcid.org/' . strtolower($orcid), $orcids)));
        $this->assertSame(array_map('strtolower', array_slice($orcids, 0, 11)), $checker->checkTextOrcids());
    }

    public function testOversizedInputIsNotExtracted(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'scielo-large-');
        $handle = fopen($file, 'w');
        ftruncate($handle, 20 * 1024 * 1024 + 1);
        fclose($handle);
        try {
            $checker = new DocumentChecker($file, $this->workingDirectory);
            $this->assertFalse($checker->extractionSucceeded());
            $this->assertSame([], $checker->checkTextOrcids());
        } finally {
            unlink($file);
        }
    }
}
