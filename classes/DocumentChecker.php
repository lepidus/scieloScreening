<?php

namespace APP\plugins\generic\scieloScreening\classes;

class DocumentChecker
{
    private $fileText = '';

    public function __construct(string $pathFile)
    {
        if (!is_file($pathFile)) {
            return;
        }

        // Write the extracted text to a private temporary file, never next to the submission file.
        $pathTxt = tempnam(sys_get_temp_dir(), 'scielo-text-');
        if ($pathTxt === false) {
            return;
        }

        try {
            $command = 'pdftotext -layout ' . escapeshellarg($pathFile) . ' ' . escapeshellarg($pathTxt) . ' 2>/dev/null';
            shell_exec($command);
            $text = file_get_contents($pathTxt);
            $this->fileText = str_replace(["\r", "\n"], '', $text === false ? '' : $text);
        } finally {
            unlink($pathTxt);
        }
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

                if (!in_array($orcid, $orcidsDetected) and $this->checksumOrcid($orcidNumbers)) {
                    $orcidsDetected[] = $orcid;
                }
            }
        }

        return $orcidsDetected;
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
