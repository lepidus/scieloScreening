<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Support\LazyCollection;
use APP\plugins\generic\scieloScreening\classes\OrcidClient;
use APP\plugins\generic\scieloScreening\classes\ScreeningExecutor;
use APP\plugins\generic\scieloScreening\tests\TestDocumentChecker;

class ScreeningExecutorTest extends TestCase
{
    private $orcids = ['0000-0001-5727-2427', '0000-0002-1648-966X'];

    private function lazyCollectionFromArray($array): LazyCollection
    {
        $lazyCollection = LazyCollection::make(function () use ($array) {
            foreach ($array as $item) {
                yield $item;
            }
        });

        return $lazyCollection;
    }

    private function getMockOrcidClient($mapOrcidResponse)
    {
        $fictionalAccessToken = 'kjh-adf-fictional-1362m';
        $worksResponses = [
            'empty' => [
                'last-modified-date' => null,
                'group' => [],
                'path' => '/0000-0001-5727-2427/works'
            ],
            'filled' => [
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
                'path' => '/0000-0001-5727-2427/works'
            ]
        ];

        $mockOrcidClient = $this->createMock(OrcidClient::class);
        $mockOrcidClient->method('getReadPublicAccessToken')->willReturn($fictionalAccessToken);
        $mockOrcidClient->method('recordHasWorks')->willReturnMap([
            [$worksResponses['empty'], false],
            [$worksResponses['filled'], true]
        ]);

        $mapOrcidWorks = [];
        foreach ($mapOrcidResponse as $orcid => $responseType) {
            $mapOrcidWorks[] = [$orcid, $fictionalAccessToken, $worksResponses[$responseType]];
        }
        $mockOrcidClient->method('getOrcidWorks')->willReturnMap($mapOrcidWorks);

        return $mockOrcidClient;
    }

    public function testOrcidsOnDocumentCheckingWhenThereIsNoDocument()
    {
        $noDocumentChecker = null;
        $screeningExecutor = new ScreeningExecutor($noDocumentChecker, null);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testThereAreNoOrcidsOnDocument()
    {
        $noTextOrcids = [];
        $documentChecker = new TestDocumentChecker($noTextOrcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, null);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testNoneOfOrcidsOnDocumentHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $orcids = [$this->orcids[0] => 'empty', $this->orcids[1] => 'empty'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('NotOkay', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testAtLeastOneOrcidOnDocumentHasWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $orcids = [$this->orcids[0] => 'filled', $this->orcids[1] => 'empty'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('Okay', $screeningExecutor->getStatusDocumentOrcids());

        $orcids = [$this->orcids[0] => 'filled', $this->orcids[1] => 'filled'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('Okay', $screeningExecutor->getStatusDocumentOrcids());
    }
}
