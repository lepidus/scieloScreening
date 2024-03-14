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

    public function testNotEnoughOrcidsOnDocument()
    {
        $noTextOrcids = [];
        $documentChecker = new TestDocumentChecker($noTextOrcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids($this->submission));

        $documentChecker = new TestDocumentChecker([$this->orcids[0]]);
        $screeningExecutor = new ScreeningExecutor($documentChecker);
        $this->assertEquals('Unable', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }

    /*public function testSomeOrcidsOnDocumentDontHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker);

        $this->assertEquals('NotOkay', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }

    public function testAllOrcidsOnDocumentHaveWorks()
    {
        $documentChecker = new TestDocumentChecker($this->orcids);
        $screeningExecutor = new ScreeningExecutor($documentChecker);

        $this->assertEquals('Okay', $screeningExecutor->getStatusDocumentOrcids($this->submission));
    }*/
}
