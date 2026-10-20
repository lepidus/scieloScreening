<?php

namespace APP\plugins\generic\scieloScreening\classes;

use Symfony\Component\Process\Process;

class DocumentChecker
{
    public const MAX_ORCIDS = 10;
    public const MAX_INPUT_BYTES = 20 * 1024 * 1024;
    public const MAX_TEXT_BYTES = 2 * 1024 * 1024;
    public const EXTRACTION_TIMEOUT = 5;
    public const EXTRACTION_MEMORY_BYTES = 256 * 1024 * 1024;
    public const EXTRACTION_CPU_SECONDS = 5;

    private $fileText = '';
    private $extractionSucceeded = false;

    public function __construct(string $pathFile, string $workingDirectory)
    {
        if (!is_file($pathFile) || !is_readable($pathFile) || filesize($pathFile) > self::MAX_INPUT_BYTES) {
            return;
        }

        if ((!is_dir($workingDirectory) && !@mkdir($workingDirectory, 0700) && !is_dir($workingDirectory)) ||
            is_link($workingDirectory) || (fileperms($workingDirectory) & 0077) !== 0) {
            return;
        }
        $lock = @fopen($workingDirectory . '/extraction.lock', 'c');
        if (!$lock) {
            return;
        }
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            return;
        }

        $pathTxt = false;
        try {
            $pathTxt = tempnam(sys_get_temp_dir(), 'scielo-text-');
            if ($pathTxt === false) {
                return;
            }
            // prlimit bounds the native parser independently of PHP's memory/time limits.
            $process = new Process([
                'prlimit',
                '--as=' . self::EXTRACTION_MEMORY_BYTES,
                '--cpu=' . self::EXTRACTION_CPU_SECONDS,
                '--fsize=' . self::MAX_TEXT_BYTES,
                '--', 'pdftotext', '-layout', $pathFile, $pathTxt,
            ]);
            $process->setTimeout(static::EXTRACTION_TIMEOUT);
            $process->disableOutput();
            $process->run();
            if (!$process->isSuccessful() || filesize($pathTxt) > self::MAX_TEXT_BYTES) {
                return;
            }
            $text = file_get_contents($pathTxt, false, null, 0, self::MAX_TEXT_BYTES + 1);
            if ($text === false || strlen($text) > self::MAX_TEXT_BYTES) {
                return;
            }
            $this->fileText = str_replace(["\r", "\n"], '', $text);
            $this->extractionSucceeded = true;
        } catch (\Symfony\Component\Process\Exception\ExceptionInterface $exception) {
            // Failed or over-budget conversion leaves the check unavailable.
        } finally {
            if ($pathTxt !== false) {
                unlink($pathTxt);
            }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function extractionSucceeded(): bool
    {
        return $this->extractionSucceeded;
    }

    public function checkTextOrcids(): array
    {
        $orcidsDetected = [];
        $text = $this->fileText;

        if (preg_match_all("~orcid\.org\/(\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x))~", $text, $matches)) {
            $orcids = $matches[1];

            foreach ($orcids as $orcid) {
                $orcid = strtolower($orcid);
                $orcidNumbers = str_replace("-", "", $orcid);

                if (!isset($orcidsDetected[$orcid]) and $this->checksumOrcid($orcidNumbers)) {
                    $orcidsDetected[$orcid] = $orcid;
                    // Keep one extra identifier to signal that the check would be incomplete.
                    if (count($orcidsDetected) > self::MAX_ORCIDS) {
                        break;
                    }
                }
            }
        }

        return array_values($orcidsDetected);
    }

    private function checksumOrcid($orcid)
    {
        $total = 0;
        for ($i = 0; $i < strlen($orcid) - 1; $i++) {
            $digit = (int) $orcid[$i];
            $total = ($total + $digit) * 2;
        }
        $remainder = $total % 11;
        $result = (12 - $remainder) % 11;

        $checksum = $result == 10 ? "x" : strval($result);
        return $checksum == $orcid[-1];
    }
}
