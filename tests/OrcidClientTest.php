<?php

use APP\plugins\generic\scieloScreening\classes\OrcidClient;
use APP\plugins\generic\scieloScreening\ScieloScreeningPlugin;
use PHPUnit\Framework\TestCase;

class OrcidClientTest extends TestCase
{
    private $emptyWorksResponse = [
        'last-modified-date' => null,
        'group' => [],
        'path' => '/0000-0001-5542-5100/works'
    ];
    private $filledWorksResponse = [
        'last-modified-date' => [
            'value' => 1710359793007
        ],
        'group' => [
            [
                'last-modified-date' => [
                    'value' => 1710359793007
                ],
                'external-ids' => [],
                'work-summary' => []
            ]
        ],
        'path' => '/0000-0001-5542-5100/works'
    ];


    public function testCheckRecordHasWorks(): void
    {
        $plugin = new ScieloScreeningPlugin();
        $contextId = 1;
        $orcidClient = new OrcidClient($plugin, $contextId);

        $this->assertFalse($orcidClient->recordHasWorks($this->emptyWorksResponse));
        $this->assertTrue($orcidClient->recordHasWorks($this->filledWorksResponse));
    }
}
