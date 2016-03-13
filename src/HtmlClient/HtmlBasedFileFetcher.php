<?php

namespace Ndthuan\FshareLib\HtmlClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\FileFetcherInterface;
use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\HtmlClient\Auth\AuthenticatorInterface;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;
use pQuery;
use pQuery\DomNode;

/**
 * Fetches downloadable file URL from an HTML page.
 */
class HtmlBasedFileFetcher implements FileFetcherInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var RequestDecoratorInterface
     */
    private $requestDecorator;

    /**
     * HtmlFileFetcher constructor.
     *
     * @param ClientInterface $httpClient
     * @param AuthenticatorInterface $authenticator
     * @param RequestDecoratorInterface $requestDecorator
     */
    public function __construct(
        ClientInterface $httpClient,
        AuthenticatorInterface $authenticator,
        RequestDecoratorInterface $requestDecorator
    ) {
        $this->httpClient = $httpClient;
        $this->authenticator = $authenticator;
        $this->requestDecorator = $requestDecorator;
    }

    /**
     * @inheritDoc
     */
    public function fetchDownloadableUrl($fileUrl)
    {
        $this->authenticator->authenticate($this->httpClient);
        $filePageDom = $this->requestFilePageDom($fileUrl);
        $fileName = trim($filePageDom->query('.file-info div:first-child')->text());
        $file = new FshareFile($fileUrl, $fileName);

        if ($filePageDom->query('#download-form')->count() < 1) {
            throw new DownloadNotFoundException('Download form not found');
        }

        $csrfToken = $filePageDom->query('#download-form [name=fs_csrf]')->val();
        $fileCode = $filePageDom->query('#DownloadForm_linkcode')->val();
        $downloadInfo = $this->requestDownloadInfo($fileCode, $csrfToken);

        if (!isset($downloadInfo['url'])) {
            throw new DownloadNotFoundException('Download URL not found');
        }

        return new DownloadableUrl(
            $downloadInfo['url'],
            $file
        );
    }

    /**
     * @param string $fileUrl
     *
     * @return DomNode
     */
    private function requestFilePageDom($fileUrl)
    {
        $request = $this->requestDecorator->decorate(new Request('GET', $fileUrl));
        $html = $this->httpClient->send($request)->getBody()->getContents();

        return pQuery::parseStr($html);
    }

    /**
     * @param string $fileCode
     * @param string $csrfToken
     *
     * @return array
     */
    private function requestDownloadInfo($fileCode, $csrfToken)
    {
        $request = $this->requestDecorator->decorate(new Request('POST', 'https://www.fshare.vn/download/get'));
        $options = [
            'form_params' => [
                'fs_csrf' => $csrfToken,
                'DownloadForm[pwd]' => '',
                'DownloadForm[linkcode]' => $fileCode,
                'ajax' => 'download-form',
            ]
        ];

        return json_decode(
            $this->httpClient->send($request, $options)->getBody()->getContents(),
            true
        );
    }
}
