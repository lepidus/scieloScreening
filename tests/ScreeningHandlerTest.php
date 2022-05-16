<?php
import('plugins.generic.scieloScreening.controllers.ScieloScreeningHandler');
import('plugins.generic.scieloScreening.classes.DOIScreening');
import('classes.submission.Submission');
import('classes.publication.Publication');
import('classes.article.Author');
use PHPUnit\Framework\TestCase;

final class ScreeningHandlerTest extends TestCase {
    private $doi = '10.1016/j.datak.2003.10.003';
    private $submission;
    private $publicationId = 1;
    private $authorGivenName = 'Altigran';
    private $authorFamilyName = 'Silva';
    private $mockResponseAPICrossref = '{"status":"ok","message-type":"work-list","message-version":"1.0.0","message":{"facets":{},"total-results":1,"items":[{"indexed":{"date-parts":[[2022,1,30]],"date-time":"2022-01-30T21:25:58Z","timestamp":1643577958226},"reference-count":23,"publisher":"Elsevier BV","issue":"2","license":[{"start":{"date-parts":[[2004,5,1]],"date-time":"2004-05-01T00:00:00Z","timestamp":1083369600000},"content-version":"tdm","delay-in-days":0,"URL":"https://www.elsevier.com/tdm/userlicense/1.0/"}],"content-domain":{"domain":[],"crossmark-restriction":false},"short-container-title":["Data & Knowledge Engineering"],"published-print":{"date-parts":[[2004,5]]},"DOI":"10.1016/j.datak.2003.10.003","type":"journal-article","created":{"date-parts":[[2003,11,7]],"date-time":"2003-11-07T14:21:42Z","timestamp":1068214902000},"page":"177-196","source":"Crossref","is-referenced-by-count":41,"title":["Automatic generation of agents for collecting hidden Web pages for data extraction"],"prefix":"10.1016","volume":"49","author":[{"given":"Juliano","family":"Palmieri Lage","sequence":"first","affiliation":[]},{"given":"Altigran S.","family":"da Silva","sequence":"additional","affiliation":[]},{"given":"Paulo B.","family":"Golgher","sequence":"additional","affiliation":[]},{"given":"Alberto H.F.","family":"Laender","sequence":"additional","affiliation":[]}],"member":"78","reference":[{"issue":"3","key":"10.1016/j.datak.2003.10.003_BIB1","doi-asserted-by":"crossref","first-page":"59","DOI":"10.1145/290593.290605","article-title":"Database techniques for the World-Wide Web: a survey","volume":"27","author":"Florescu","year":"1998","journal-title":"SIGMOD Record"},{"issue":"4","key":"10.1016/j.datak.2003.10.003_BIB2","doi-asserted-by":"crossref","first-page":"98","DOI":"10.1126/science.280.5360.98","article-title":"Searching the World-Wide Web","volume":"280","author":"Lawrence","year":"1998","journal-title":"Science"},{"key":"10.1016/j.datak.2003.10.003_BIB3","doi-asserted-by":"crossref","unstructured":"M.K. Bergman, The deep Web: Surfacing hidden value, White Paper, Bright Planet, 2000","DOI":"10.3998/3336451.0007.104"},{"issue":"3","key":"10.1016/j.datak.2003.10.003_BIB4","doi-asserted-by":"crossref","first-page":"227","DOI":"10.1016/S0169-023X(99)00027-0","article-title":"Conceptual-model-based data extraction from multiple-record Web pages","volume":"31","author":"Embley","year":"1999","journal-title":"Data and Knowledge Engineering"},{"issue":"8","key":"10.1016/j.datak.2003.10.003_BIB5","doi-asserted-by":"crossref","first-page":"539","DOI":"10.1016/S0306-4379(98)00028-3","article-title":"Grammars have exceptions","volume":"23","author":"Crescenzi","year":"1998","journal-title":"Information Systems"},{"key":"10.1016/j.datak.2003.10.003_BIB6","doi-asserted-by":"crossref","unstructured":"B. Adelberg, NoDoSE––A tool for semi-automatically extracting structured and semistructured data from text documents, in: Proceedings of the ACM SIGMOD International Conference on Management of Data, Seattle, USA, 1998, pp. 283–294","DOI":"10.1145/276304.276330"},{"key":"10.1016/j.datak.2003.10.003_BIB7","unstructured":"V. Crescenzi, G. Mecca, P. Merialdo, RoadRunner: Towards automatic data extraction from large Web sites, in: Proceedings of the 27th International Conference on Very Large Data Bases, Rome, Italy, 2001, pp. 109–118"},{"issue":"8","key":"10.1016/j.datak.2003.10.003_BIB8","doi-asserted-by":"crossref","first-page":"521","DOI":"10.1016/S0306-4379(98)00027-1","article-title":"Generating finite-state transducers for semi-structured data extraction from the Web","volume":"23","author":"Hsu","year":"1998","journal-title":"Information Systems"},{"issue":"1–2","key":"10.1016/j.datak.2003.10.003_BIB9","doi-asserted-by":"crossref","first-page":"15","DOI":"10.1016/S0004-3702(99)00100-9","article-title":"Wrapper induction: Efficiency and expressiveness","volume":"118","author":"Kushmerick","year":"2000","journal-title":"Artificial Intelligence Journal"},{"issue":"2","key":"10.1016/j.datak.2003.10.003_BIB10","doi-asserted-by":"crossref","first-page":"121","DOI":"10.1016/S0169-023X(01)00047-7","article-title":"DEByE––Data Extraction By Example","volume":"40","author":"Laender","year":"2002","journal-title":"Data and Knowledge Engineering"},{"key":"10.1016/j.datak.2003.10.003_BIB11","doi-asserted-by":"crossref","unstructured":"L. Liu, C. Pu, W. Han, XWRAP: An XML-enabled wrapper construction system for Web information sources, in: Proceedings of the 16th International Conference on Data Engineering, San Diego, USA, 2000, pp. 611–621","DOI":"10.1109/ICDE.2000.839475"},{"issue":"1/2","key":"10.1016/j.datak.2003.10.003_BIB12","doi-asserted-by":"crossref","first-page":"93","DOI":"10.1023/A:1010022931168","article-title":"Hierarchical wrapper induction for semistructured information sources","volume":"4","author":"Muslea","year":"2001","journal-title":"Autonomous Agents and Multi-Agent Systems"},{"issue":"3","key":"10.1016/j.datak.2003.10.003_BIB13","doi-asserted-by":"crossref","first-page":"283","DOI":"10.1016/S0169-023X(00)00051-3","article-title":"Building intelligent Web applications using lightweight wrappers","volume":"36","author":"Sahuguet","year":"2001","journal-title":"Data and Knowledge Engineering"},{"issue":"2","key":"10.1016/j.datak.2003.10.003_BIB14","doi-asserted-by":"crossref","first-page":"84","DOI":"10.1145/565117.565137","article-title":"A brief survey of Web data extraction tools","volume":"31","author":"Laender","year":"2002","journal-title":"SIGMOD Record"},{"key":"10.1016/j.datak.2003.10.003_BIB15","doi-asserted-by":"crossref","unstructured":"R.B. Doorembos, O. Etzioni, D. Weld, A scalable comparison-shopping agent for the World-Wide Web, in: Proceedings of the First International Conference on Autonomous Agents, Marina del Rey, USA, 1997, pp. 39–48","DOI":"10.1145/267658.267666"},{"key":"10.1016/j.datak.2003.10.003_BIB16","unstructured":"S. Liddle, D. Embley, D. Scott, S. Yau, Extracting data behind Web forms, in: Proceedings of the Workshop on Conceptual Modeling Approaches for e-Business, Tampere, Finland, 2002, pp. 38–49"},{"key":"10.1016/j.datak.2003.10.003_BIB17","doi-asserted-by":"crossref","unstructured":"G. Modica, A. Gal, H.M. Jamil, The use of machine-generated ontologies in dynamic information seeking, in: Proceedings of the 9th International Conference on Cooperative Information Systems, Trento, Italy, 2001, pp. 433–448","DOI":"10.1007/3-540-44751-2_32"},{"key":"10.1016/j.datak.2003.10.003_BIB18","unstructured":"S. Raghavan, H. Garcia-Molina, Crawling the hidden Web, in: Proceedings of the 27th International Conference on Very Large Data Bases, Roma, Italy, 2001, pp. 129–138"},{"key":"10.1016/j.datak.2003.10.003_BIB19","doi-asserted-by":"crossref","unstructured":"H. Davulcu, J. Freire, M. Kifer, I.V. Ramakrishnan, A layered architecture for querying dynamic Web content, in: Proceedings of the ACM SIGMOD International Conference on Management of Data, Philadelphia, USA, 1999, pp. 491–502","DOI":"10.1145/304182.304225"},{"key":"10.1016/j.datak.2003.10.003_BIB20","doi-asserted-by":"crossref","unstructured":"P.B. Golgher, A.H.F. Laender, A.S. da Silva, B. Ribeiro-Neto, An example-based environment for wrapper generation., in: Proceedings of the 2nd International Workshop on The World Wide Web and Conceptual Modeling, Salt Lake City, USA, 2000, pp. 152–164","DOI":"10.1007/3-540-45394-6_14"},{"key":"10.1016/j.datak.2003.10.003_BIB21","doi-asserted-by":"crossref","unstructured":"P.B. Golgher, A.S. da Silva, A.H.F. Laender, B. Ribeiro-Neto, Bootstrapping for example-based data extraction, in: Proceedings of the 10th ACM International Conference on Information and Knowledge Management, Atlanta, USA, 2001, pp. 371–378","DOI":"10.1145/502585.502648"},{"key":"10.1016/j.datak.2003.10.003_BIB22","series-title":"Modern Information Retrieval","author":"Baeza-Yates","year":"1999"},{"key":"10.1016/j.datak.2003.10.003_BIB23","doi-asserted-by":"crossref","unstructured":"P.P. Calado, M.A. Gonçalves, E.A. Fox, B. Ribeiro-Neto, A.H.F. Laender, A.S. da Silva, D.C. Reis, P.A. Roberto, M.V. Vieira, J.P. Lage, The Web-DL environment for building digital libraries from the Web, in: Proceedings of the 2003 Joint Conference on Digital Libraries, Huston, USA, 2003, pp. 346–360","DOI":"10.1109/JCDL.2003.1204887"}],"container-title":["Data & Knowledge Engineering"],"language":"en","link":[{"URL":"https://api.elsevier.com/content/article/PII:S0169023X03001769?httpAccept=text/xml","content-type":"text/xml","content-version":"vor","intended-application":"text-mining"},{"URL":"https://api.elsevier.com/content/article/PII:S0169023X03001769?httpAccept=text/plain","content-type":"text/plain","content-version":"vor","intended-application":"text-mining"}],"deposited":{"date-parts":[[2020,3,26]],"date-time":"2020-03-26T17:06:14Z","timestamp":1585242374000},"score":0,"issued":{"date-parts":[[2004,5]]},"references-count":23,"journal-issue":{"issue":"2","published-print":{"date-parts":[[2004,5]]}},"alternative-id":["S0169023X03001769"],"URL":"http://dx.doi.org/10.1016/j.datak.2003.10.003","ISSN":["0169-023X"],"issn-type":[{"value":"0169-023X","type":"print"}],"subject":["Information Systems and Management"],"published":{"date-parts":[[2004,5]]}}],"items-per-page":20,"query":{"start-index":0,"search-terms":null}}}';
    private $expectedAuthors = ["Juliano Palmieri Lage", "Altigran S. da Silva", "Paulo B. Golgher", "Alberto H.F. Laender"];

    public function setUp(): void {
        parent::setUp();
        $this->submission = $this->createSubmissionWithAuthors();
    }

    private function createSubmissionWithAuthors() {
        $submission = new Submission();
        $publication = new Publication();
        
        $author = new Author();        
        $author->setData('givenName', ['en_US' => $this->authorGivenName]);
		$author->setData('familyName', ['en_US' => $this->authorFamilyName]);
        
        $publication->setData('id', $this->publicationId);
        $publication->setData('authors', [$author]);
        $submission->setData('publications', [$publication]);
        $submission->setData('currentPublicationId', $this->publicationId);

        return $submission;
    }

    public function testGetAuthorsNamesFromDOI(): void {
        $screeningHandler = new ScieloScreeningHandler();

        $this->assertEquals($this->expectedAuthors, $screeningHandler->getDoiAuthorsNames($this->mockResponseAPICrossref));
    }

    public function testStatusDOIsReturnsAuthorsNamesWhenAuthorshipNotConfirmed(): void {
        $doiObj = new DOIScreening();
        $doiObj->setDOICode($this->doi);
        $doiObj->setConfirmedAuthorship(false);
        
        $screeningHandler = new ScieloScreeningHandler();
        $statusDOI = $screeningHandler->getStatusDOI($this->submission, [$doiObj]);
        $authorFullName = $this->authorGivenName . " " . $this->authorFamilyName;
        $implodedAuthors = implode(", ", $this->expectedAuthors);

        $this->assertEquals($authorFullName, $statusDOI['authorFromSubmission']);
        $this->assertEquals($implodedAuthors, $statusDOI['authorsFromDOIs'][0]);
    }

    public function testDOIAuthorshipAnySubmissionAuthor(): void {
        $dummyAuthor = new Author();        
        $dummyAuthor->setData('givenName', ['en_US' => 'Peewee']);
		$dummyAuthor->setData('familyName', ['en_US' => 'Herman']);
        $rightAuthor = new Author();        
        $rightAuthor->setData('givenName', ['en_US' => 'Altigran']);
		$rightAuthor->setData('familyName', ['en_US' => 'S. da Silva']);

        $publication = $this->submission->getData('publications')[0];
        $publication->setData('authors', [$dummyAuthor, $rightAuthor]);

        $screeningHandler = new ScieloScreeningHandler();
        $responseJson = json_decode($this->mockResponseAPICrossref, true);
        $confirmedAuthorship = $screeningHandler->checkDOIAuthorship($this->submission, $responseJson);

        $this->assertTrue($confirmedAuthorship);
    }
}