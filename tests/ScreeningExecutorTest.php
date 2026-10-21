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

    public function testSubmissionValidationDoesNotRunDocumentOrcidChecks(): void
    {
        $previousContainer = \Illuminate\Container\Container::getInstance();
        $container = new \Illuminate\Container\Container();
        \Illuminate\Container\Container::setInstance($container);
        try {
            $collector = $this->createMock(\PKP\galley\Collector::class);
            $collector->method('filterByPublicationIds')->with([42])->willReturnSelf();
            $collector->method('getMany')->willReturn(LazyCollection::make([]));
            $repository = $this->createMock(\PKP\galley\Repository::class);
            $repository->method('getCollector')->willReturn($collector);
            $container->instance(\PKP\galley\Repository::class, $repository);
            $publication = new \APP\publication\Publication();
            $publication->setId(42);
            $publication->setData('authors', LazyCollection::make([]));
            foreach (['title', 'abstract', 'keywords'] as $field) {
                $publication->setData($field, 'present', 'en');
            }
            $submission = $this->createMock(\APP\submission\Submission::class);
            $submission->method('getCurrentPublication')->willReturn($publication);
            $document = $this->createMock(\APP\plugins\generic\scieloScreening\classes\DocumentChecker::class);
            $document->expects($this->never())->method('extractionSucceeded');
            $orcid = $this->createMock(OrcidClient::class);
            $orcid->expects($this->never())->method('getReadPublicAccessToken');
            $executor = new ScreeningExecutor($document, $orcid);
            $data = $executor->getSubmissionValidationData($submission);
            $this->assertArrayNotHasKey('statusDocumentOrcids', $data);
            $this->assertTrue($data['statusMetadataEnglish']);
            $this->assertFalse($data['statusPDFs']);
            $this->assertSame(0, $data['numPDFs']);
            $this->assertArrayHasKey('statusOrcid', $data);
        } finally {
            \Illuminate\Container\Container::setInstance($previousContainer);
        }
    }

    public function testOrcidsOnDocumentCheckingWhenThereIsNoDocument()
    {
        $noDocumentChecker = null;
        $screeningExecutor = new ScreeningExecutor($noDocumentChecker, null);
        $this->assertEquals('UnableNoFile', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testFailedExtractionDoesNotQueryOrcid(): void
    {
        $documentChecker = $this->createMock(\APP\plugins\generic\scieloScreening\classes\DocumentChecker::class);
        $documentChecker->method('extractionSucceeded')->willReturn(false);
        $documentChecker->method('getFailureStatus')->willReturn('UnableExtraction');
        $documentChecker->expects($this->never())->method('checkTextOrcids');
        $orcidClient = $this->createMock(OrcidClient::class);
        $orcidClient->expects($this->never())->method('getReadPublicAccessToken');
        $executor = new ScreeningExecutor($documentChecker, $orcidClient);
        $this->assertSame('UnableExtraction', $executor->getStatusDocumentOrcids());
    }

    public function testThereAreNoOrcidsOnDocument()
    {
        $noTextOrcids = [];
        $documentChecker = new TestDocumentChecker($noTextOrcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, null);
        $this->assertEquals('UnableNoOrcids', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testNoneOfOrcidsOnDocumentHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $orcids = [$this->orcids[0] => 'empty', $this->orcids[1] => 'empty'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('NotOkay', $screeningExecutor->getStatusDocumentOrcids());
    }

    public function testDocumentCannotCauseMoreThanTenWorksRequests(): void
    {
        $documentChecker = new TestDocumentChecker(array_map('strval', range(1, 100)));
        $orcidClient = $this->createMock(OrcidClient::class);
        $orcidClient->expects($this->once())->method('getReadPublicAccessToken')->willReturn('token');
        $orcidClient->expects($this->exactly(10))->method('getOrcidWorks')->willReturn(['group' => []]);
        $orcidClient->method('recordHasWorks')->willReturn(false);
        $executor = new ScreeningExecutor($documentChecker, $orcidClient);
        $this->assertSame('UnableOrcidLimit', $executor->getStatusDocumentOrcids());
    }

    public function testExactlyTenEmptyRecordsCanBeReportedAsNotOkay(): void
    {
        $documentChecker = new TestDocumentChecker(array_map('strval', range(1, 10)));
        $orcidClient = $this->createMock(OrcidClient::class);
        $orcidClient->expects($this->exactly(10))->method('getOrcidWorks')->willReturn(['group' => []]);
        $orcidClient->method('recordHasWorks')->willReturn(false);
        $executor = new ScreeningExecutor($documentChecker, $orcidClient);
        $this->assertSame('NotOkay', $executor->getStatusDocumentOrcids());
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
