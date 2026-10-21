<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ReleaseWorkflowTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/scielo-release-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
        $this->command('sudo', 'exit 0');
        $this->command('date', 'printf "%s" "$TEST_DATE"');
        $this->command('xmlstarlet', <<<'SH'
case "$4" in
    version/application) printf "%s" "$PLUGIN_NAME" ;;
    version/release) printf "%s" "$TEST_RELEASE" ;;
    version/date) printf "%s" "$TEST_DATE" ;;
    *) exit 1 ;;
esac
SH);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
    }

    private function command(string $name, string $body): void
    {
        file_put_contents($this->directory . '/' . $name, "#!/bin/sh\n" . $body . "\n");
        chmod($this->directory . '/' . $name, 0700);
    }

    private function checkRelease(string $ref): Process
    {
        $workflow = file_get_contents(dirname(__DIR__) . '/.github/workflows/generate-package.yml');
        $this->assertSame(1, preg_match('/      - name: Check version\.xml\n(.*?)(?=      - name:|\z)/s', $workflow, $step));
        $this->assertSame(1, preg_match('/        run: \|\n((?:          .*\n)+)/', $step[1], $block));
        $script = preg_replace('/^          /m', '', $block[1]);
        // Model GitHub's expression rendering before Bash parses the run block.
        $script = str_replace('${{ github.ref }}', $ref, $script);
        $process = new Process(['bash', '-c', $script], dirname(__DIR__), [
            'PATH' => $this->directory . ':' . getenv('PATH'),
            'GITHUB_REF' => $ref,
            'PLUGIN_NAME' => 'scieloScreening',
            'TEST_RELEASE' => '3.0.2.0',
            'TEST_DATE' => '2026-10-21',
        ]);
        $process->setTimeout(5);
        $process->run();
        return $process;
    }

    public function testTagCannotExecuteShellCommands(): void
    {
        $marker = $this->directory . '/injected';
        $ref = 'refs/tags/v$(touch${IFS}' . $marker . ')';
        (new Process(['git', 'check-ref-format', $ref]))->mustRun();
        $process = $this->checkRelease($ref);
        $this->assertFileDoesNotExist($marker, 'A valid Git tag executed a command in the release check');
        $this->assertFalse($process->isSuccessful());
    }

    public function testIncompleteAndMalformedVersionTagsAreRejected(): void
    {
        foreach (['v', 'v3', 'v3.0', 'v3.0.2', 'v3.0.2.00', 'v3.0.2.0-rc1', 'v3.0.2.0-extra', 'v9'] as $tag) {
            $process = $this->checkRelease('refs/tags/' . $tag);
            $this->assertFalse($process->isSuccessful(), 'Unexpectedly accepted release tag: ' . $tag);
        }
    }

    public function testExactReleaseTagPasses(): void
    {
        $process = $this->checkRelease('refs/tags/v3.0.2.0');
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
    }
}
