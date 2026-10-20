<?php

use APP\plugins\generic\scieloScreening\classes\OrcidClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;

class OrcidEndpointTest extends TestCase
{
    private function client($url, array &$history, ?callable $clock = null): OrcidClient
    {
        $plugin = new class ($url) {
            public function __construct(private $url)
            {
            }
            public function getSetting($contextId, $setting)
            {
                return $setting === 'orcidAPIPath' ? $this->url : 'test-credential';
            }
        };
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"access_token":"test-token"}'),
            new Response(200, [], '{"group":[]}'),
        ]));
        $stack->push(Middleware::history($history));
        $client = $this->getMockBuilder(OrcidClient::class)->setConstructorArgs([$plugin, 1])->onlyMethods($clock ? ['getHttpClient', 'getMonotonicTime'] : ['getHttpClient'])->getMock();
        if ($clock) {
            $client->method('getMonotonicTime')->willReturnCallback($clock);
        }
        $client->method('getHttpClient')->willReturn(new Client(['handler' => $stack]));
        return $client;
    }

    public function testArbitraryDestinationsAreBlockedBeforeSendingCredentials(): void
    {
        foreach (['http://127.0.0.1/', 'https://example.test/', 'https://pub.orcid.org.evil.test/', 'https://pub.orcid.org@evil.test/', 'https://pub.orcid.org/?next=', 'https://pub.orcid.org:444/', null, ['https://pub.orcid.org/']] as $url) {
            $history = [];
            $client = $this->client($url, $history);
            try {
                $client->getReadPublicAccessToken();
                $this->fail('Untrusted token destination accepted');
            } catch (TransferException $exception) {
                $this->assertSame([], $history);
            }
            try {
                $client->getOrcidWorks('0000-0001-5727-2427', 'test-token');
                $this->fail('Untrusted works destination accepted');
            } catch (TransferException $exception) {
                $this->assertSame([], $history);
            }
        }
    }

    public function testTotalBudgetIncludesTokenAndWorksRequests(): void
    {
        $history = [];
        $times = [0.0, 0.0, 9.5, 10.1];
        $client = $this->client(OrcidClient::ORCID_API_URL_PUBLIC, $history, function () use (&$times) { return array_shift($times); });
        $client->getReadPublicAccessToken();
        $client->getOrcidWorks('0000-0001-5727-2427', 'test-token');
        $this->assertSame(2.0, $history[0]['options']['timeout']);
        $this->assertSame(0.5, $history[1]['options']['timeout']);
        try {
            $client->getOrcidWorks('0000-0002-1648-966X', 'test-token');
            $this->fail('Total deadline was ignored');
        } catch (TransferException $exception) {
            $this->assertCount(2, $history);
        }
    }

    public function testInvalidEndpointCannotBePersisted(): void
    {
        $form = $this->getMockBuilder(\APP\plugins\generic\scieloScreening\ScieloScreeningSettingsForm::class)->disableOriginalConstructor()->onlyMethods(['orcidIsGloballyConfigured'])->getMock();
        $form->method('orcidIsGloballyConfigured')->willReturn(false);
        $form->setData('orcidAPIPath', 'http://127.0.0.1/');
        $plugin = $this->createMock(\APP\plugins\generic\scieloScreening\ScieloScreeningPlugin::class);
        $plugin->expects($this->never())->method('updateSetting');
        $form->plugin = $plugin;
        $this->expectException(InvalidArgumentException::class);
        $form->execute();
    }

    public function testAllSupportedEndpointsStillWorkAndDisableRedirects(): void
    {
        foreach ([OrcidClient::ORCID_API_URL_PUBLIC, OrcidClient::ORCID_API_URL_PUBLIC_SANDBOX, OrcidClient::ORCID_API_URL_MEMBER, OrcidClient::ORCID_API_URL_MEMBER_SANDBOX] as $url) {
            $history = [];
            $client = $this->client($url, $history);
            $this->assertSame('test-token', $client->getReadPublicAccessToken());
            $this->assertSame(['group' => []], $client->getOrcidWorks('0000-0001-5727-2427', 'test-token'));
            $this->assertSame($url . 'oauth/token', (string) $history[0]['request']->getUri());
            $this->assertSame($url . 'v3.0/0000-0001-5727-2427/works', (string) $history[1]['request']->getUri());
            $this->assertFalse($history[0]['options']['allow_redirects']);
            $this->assertFalse($history[1]['options']['allow_redirects']);
            $this->assertLessThanOrEqual(2, $history[0]['options']['timeout']);
            $this->assertLessThanOrEqual(2, $history[1]['options']['timeout']);
            $this->assertSame(1, $history[0]['options']['connect_timeout']);
        }
    }
}
