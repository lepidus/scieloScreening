<?php

namespace APP\plugins\generic\scieloScreening\classes;

use APP\core\Application;
use APP\plugins\generic\scieloScreening\classes\APIKeyEncryption;

class OrcidClient
{
    public const ORCID_URL = 'https://orcid.org/';
    public const ORCID_URL_SANDBOX = 'https://sandbox.orcid.org/';
    public const ORCID_API_URL_PUBLIC = 'https://pub.orcid.org/';
    public const ORCID_API_URL_PUBLIC_SANDBOX = 'https://pub.sandbox.orcid.org/';
    public const ORCID_API_URL_MEMBER = 'https://api.orcid.org/';
    public const ORCID_API_URL_MEMBER_SANDBOX = 'https://api.sandbox.orcid.org/';
    public const API_URLS = [
        self::ORCID_API_URL_PUBLIC,
        self::ORCID_API_URL_PUBLIC_SANDBOX,
        self::ORCID_API_URL_MEMBER,
        self::ORCID_API_URL_MEMBER_SANDBOX,
    ];
    public const ORCID_API_SCOPE_PUBLIC = '/authenticate';
    public const ORCID_API_SCOPE_MEMBER = '/activities/update';

    private $plugin;
    private $contextId;

    public function __construct($plugin, $contextId)
    {
        $this->plugin = $plugin;
        $this->contextId = $contextId;
    }

    private function getPluginSetting($settingName)
    {
        $settingValue = $this->plugin->getSetting($this->contextId, $settingName);
        if ($settingName == 'orcidClientId' || $settingName == 'orcidClientSecret') {
            $encrypter = new APIKeyEncryption();
            if (!empty($settingValue) && $encrypter->textIsEncrypted($settingValue)) {
                $settingValue = $encrypter->decryptString($settingValue);
            }
        }

        return $settingValue;
    }

    public static function isAllowedApiUrl($url): bool
    {
        return in_array($url, self::API_URLS, true);
    }

    private function getApiUrl(): string
    {
        $url = $this->getPluginSetting('orcidAPIPath');
        if (!self::isAllowedApiUrl($url)) {
            throw new \GuzzleHttp\Exception\TransferException('Invalid ORCID API endpoint');
        }
        return $url;
    }

    protected function getHttpClient(): \GuzzleHttp\Client
    {
        return Application::get()->getHttpClient();
    }

    public function getReadPublicAccessToken(): string
    {
        $tokenUrl = $this->getApiUrl() . 'oauth/token';
        $httpClient = $this->getHttpClient();
        $requestHeaders = ['Accept' => 'application/json'];
        $requestData = [
            'client_id' => $this->getPluginSetting('orcidClientId'),
            'client_secret' => $this->getPluginSetting('orcidClientSecret'),
            'grant_type' => 'client_credentials',
            'scope' => '/read-public'
        ];

        $response = $httpClient->request(
            'POST',
            $tokenUrl,
            [
                'allow_redirects' => false,
                'headers' => $requestHeaders,
                'form_params' => $requestData,
            ]
        );

        $responseJson = json_decode($response->getBody(), true);
        return $responseJson['access_token'];
    }

    public function getOrcidWorks(string $orcid, string $accessToken): array
    {
        $worksUrl = $this->getApiUrl() . 'v3.0/' . urlencode($orcid) . '/works';
        $httpClient = $this->getHttpClient();
        $response = $httpClient->request(
            'GET',
            $worksUrl,
            [
                'allow_redirects' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    public function recordHasWorks(array $worksResponse): bool
    {
        return !empty($worksResponse['group']);
    }
}
