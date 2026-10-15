<?php

use APP\plugins\generic\scieloScreening\classes\DocumentChecker;
use PHPUnit\Framework\TestCase;

class FastDocumentChecker extends DocumentChecker
{
    public const EXTRACTION_TIMEOUT = 0.3;
}

class DocumentExtractionLimitsTest extends TestCase
{
    private $directory;
    private $previousPath;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/scielo-limits-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
        $this->previousPath = getenv('PATH');
        putenv('PATH=' . $this->directory . ':' . $this->previousPath);
    }

    protected function tearDown(): void
    {
        putenv('PATH=' . $this->previousPath);
        foreach (glob($this->directory . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
    }

    private function fakeConverter(string $body): void
    {
        file_put_contents($this->directory . '/pdftotext', "#!/bin/sh\n" . $body);
        chmod($this->directory . '/pdftotext', 0700);
    }

    private function checkFailure(string $body): void
    {
        $this->fakeConverter($body);
        $before = glob(sys_get_temp_dir() . '/scielo-text-*');
        $checker = new FastDocumentChecker(__DIR__ . '/assets/orcids_document.pdf');
        $this->assertFalse($checker->extractionSucceeded());
        $this->assertSame([], $checker->checkTextOrcids());
        $this->assertSame($before, glob(sys_get_temp_dir() . '/scielo-text-*'));
    }

    public function testOutputLimitAndCleanup(): void
    {
        $this->checkFailure('exec head -c 3145728 /dev/zero > "$3"');
    }

    public function testTimeoutAndCleanup(): void
    {
        $start = microtime(true);
        $this->checkFailure('exec sleep 10');
        $this->assertLessThan(2, microtime(true) - $start);
    }

    public function testAddressSpaceLimitAndCleanup(): void
    {
        // Allocation would succeed without the native process address-space limit.
        $this->checkFailure('exec php -d memory_limit=-1 -r \'$data = str_repeat("x", 300 * 1024 * 1024);\'');
    }

    public function testConcurrentConversionIsSkipped(): void
    {
        $this->fakeConverter('touch "' . $this->directory . '/started"');
        $reflection = new ReflectionClass(DocumentChecker::class);
        $lock = fopen(sys_get_temp_dir() . '/scielo-screening-' . hash('sha256', dirname($reflection->getFileName())) . '.lock', 'c');
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        try {
            $checker = new DocumentChecker(__DIR__ . '/assets/orcids_document.pdf');
            $this->assertFalse($checker->extractionSucceeded());
            $this->assertFileDoesNotExist($this->directory . '/started');
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
