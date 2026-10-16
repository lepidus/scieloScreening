<?php

namespace APP\plugins\generic\scieloScreening\tests;

class TestDocumentChecker
{
    private $textOrcids;

    public function __construct(array $textOrcids)
    {
        $this->textOrcids = $textOrcids;
    }

    public function checkTextOrcids(): array
    {
        return $this->textOrcids;
    }
}
