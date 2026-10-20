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
        $checker = new FastDocumentChecker(__DIR__ . '/assets/orcids_document.pdf', $this->directory);
        $this->assertFalse($checker->extractionSucceeded());
        $this->assertSame([], $checker->checkTextOrcids());
        $this->assertSame($before, glob(sys_get_temp_dir() . '/scielo-text-*'));
    }

    public function testMissingConverterToolsAreReported(): void
    {
        putenv('PATH=' . $this->directory);
        $checker = new DocumentChecker(__DIR__ . '/assets/orcids_document.pdf', $this->directory);
        $this->assertSame('UnableRequirements', $checker->getFailureStatus());
    }

    public function testDisabledProcOpenIsReported(): void
    {
        $process = new \Symfony\Component\Process\Process([
            PHP_BINARY, '-d', 'disable_functions=proc_open', '-r',
            'require $argv[1]; $checker = new \APP\plugins\generic\scieloScreening\classes\DocumentChecker($argv[2], $argv[3]); echo $checker->getFailureStatus();',
            __DIR__ . '/bootstrap.php', __DIR__ . '/assets/orcids_document.pdf', $this->directory,
        ]);
        $process->mustRun();
        $this->assertSame('UnableRequirements', $process->getOutput());
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

    public function testConcurrentConversionWaitsForThePreviousConverter(): void
    {
        $this->fakeConverter('printf "orcid.org/0000-0001-5727-2427" > "$3"');
        $process = new \Symfony\Component\Process\Process([
            PHP_BINARY, '-r',
            '$lock = fopen($argv[1], "c"); flock($lock, LOCK_EX); file_put_contents($argv[2], "ready"); usleep(300000);',
            $this->directory . '/extraction.lock', $this->directory . '/ready',
        ]);
        $process->start();
        try {
            $deadline = microtime(true) + 2;
            while (!file_exists($this->directory . '/ready') && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertFileExists($this->directory . '/ready');
            $checker = new DocumentChecker(__DIR__ . '/assets/orcids_document.pdf', $this->directory);
            $this->assertTrue($checker->extractionSucceeded());
            $this->assertSame(['0000-0001-5727-2427'], $checker->checkTextOrcids());
        } finally {
            $process->wait();
        }
    }

    public function testConcurrentConversionStopsWaitingAtTheDeadline(): void
    {
        $this->fakeConverter('touch "' . $this->directory . '/started"');
        $lock = fopen($this->directory . '/extraction.lock', 'c');
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        try {
            $start = microtime(true);
            $checker = new DocumentChecker(__DIR__ . '/assets/orcids_document.pdf', $this->directory);
            $this->assertGreaterThanOrEqual(DocumentChecker::LOCK_WAIT_SECONDS, microtime(true) - $start);
            $this->assertLessThan(DocumentChecker::LOCK_WAIT_SECONDS + 1, microtime(true) - $start);
            $this->assertFalse($checker->extractionSucceeded());
            $this->assertSame('UnableBusy', (new \APP\plugins\generic\scieloScreening\classes\ScreeningExecutor($checker, null))->getStatusDocumentOrcids());
            $this->assertFileDoesNotExist($this->directory . '/started');
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
