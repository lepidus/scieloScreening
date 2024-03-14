<?php

use PHPUnit\Framework\TestCase;
use APP\submission\Submission;
use APP\publication\Publication;
use APP\author\Author;
use Illuminate\Support\LazyCollection;
use APP\plugins\generic\scieloScreening\classes\OrcidClient;
use APP\plugins\generic\scieloScreening\classes\ScreeningExecutor;
use APP\plugins\generic\scieloScreening\tests\TestDocumentChecker;

class ScreeningExecutorTest extends TestCase
{
    private $submission;
    private $orcids = ['0000-0001-5727-2427', '0000-0002-1648-966X'];

    public function setUp(): void
    {
        parent::setUp();
        $this->submission = $this->createTestSubmission();
    }

    private function createTestSubmission(): Submission
    {
        $author1 = new Author();
        $author1->setData('orcid', 'https://orcid.org/' . $this->orcids[0]);
        $author2 = new Author();
        $author2->setData('orcid', 'https://orcid.org/' . $this->orcids[1]);
        $authorsCollection = $this->lazyCollectionFromArray([$author1, $author2]);

        $publicationId = 1408;
        $publication = new Publication();
        $publication->setData('id', $publicationId);
        $publication->setData('authors', $authorsCollection);

        $submission = new Submission();
        $submission->setData('publications', [$publication]);
        $submission->setData('currentPublicationId', $publicationId);

        return $submission;
    }

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

    public function testNotEnoughOrcidsOnDocument()
    {
        $noTextOrcids = [];
        $documentChecker = new TestDocumentChecker($noTextOrcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, null);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids($this->submission));

        $documentChecker = new TestDocumentChecker([$this->orcids[0]]);
        $screeningExecutor = new ScreeningExecutor($documentChecker, null);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }

    public function testSomeOrcidsOnDocumentDontHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $orcids = [$this->orcids[0] => 'empty', $this->orcids[1] => 'empty'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('NotOkay', $screeningExecutor->getStatusDocumentOrcids($this->submission));

        $orcids = [$this->orcids[0] => 'filled', $this->orcids[1] => 'empty'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('NotOkay', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }

    public function testAllOrcidsOnDocumentHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $orcids = [$this->orcids[0] => 'filled', $this->orcids[1] => 'filled'];
        $orcidClient = $this->getMockOrcidClient($orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);

        $this->assertEquals('Okay', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }
}
