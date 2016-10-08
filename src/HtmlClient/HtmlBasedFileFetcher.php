<?php

namespace Ndthuan\FshareLib\HtmlClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\Api\FileFetcherInterface;
use Ndthuan\FshareLib\HtmlClient\Auth\AuthenticatorInterface;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;
use pQuery;
use pQuery\DomNode;
use Psr\Http\Message\ResponseInterface;

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

        $response = $this->requestFilePage($fileUrl);

        if ($this->isDirectDownload($response)) {
            $downloadUrl = $response->getHeaderLine('location');
            $fshareFile = new FshareFile($fileUrl, basename(urldecode($downloadUrl)));
        } else {
            $filePageDom = $this->parseFilePageDom($response);
            $this->preventInvalidFilePage($filePageDom);
            $downloadUrl = $this->fetchDownloadUrl($filePageDom);
            $fshareFile = new FshareFile($fileUrl, $this->fetchFileName($filePageDom));
        }

        return new DownloadableUrl($downloadUrl, $fshareFile);
    }

    /**
     * @inheritDoc
     */
    public function fetchFileInfo($fileUrl)
    {
        $response = $this->requestFilePage($fileUrl);

        if ($this->isDirectDownload($response)) {
            $downloadUrl = $response->getHeaderLine('location');
            $fshareFile = new FshareFile($fileUrl, basename(urldecode($downloadUrl)));
        } else {
            $filePageDom = $this->parseFilePageDom($response);
            $fshareFile = new FshareFile($fileUrl, $this->fetchFileName($filePageDom));
        }

        return $fshareFile;
    }

    /**
     * Validate check throw exceptions if the page DOM is not eligible.
     *
     * @param DomNode $filePageDom
     *
     * @throws DownloadFormNotFoundException
     */
    private function preventInvalidFilePage(DomNode $filePageDom)
    {
        if ($filePageDom->query('#download-form')->count() < 1) {
            throw new DownloadFormNotFoundException('Download form not found');
        }
    }

    /**
     * @param DomNode $filePageDom
     *
     * @return string
     *
     * @throws DownloadUrlNotFoundException
     */
    private function fetchDownloadUrl(DomNode $filePageDom)
    {
        $downloadInfo = $this->requestDownloadInfo(
            $this->fetchFileLinkCode($filePageDom),
            $this->fetchCsrfToken($filePageDom)
        );

        if (!isset($downloadInfo['url'])) {
            throw new DownloadUrlNotFoundException('Download URL not found');
        }

        return $downloadInfo['url'];
    }

    /**
     * @param DomNode $filePageDom
     *
     * @return string
     */
    private function fetchFileName(DomNode $filePageDom)
    {
        return trim($filePageDom->query('.file-info div:first-child')->text());
    }

    /**
     * @param DomNode $filePageDom
     *
     * @return string
     */
    private function fetchCsrfToken(DomNode $filePageDom)
    {
        return $filePageDom->query('#download-form [name=fs_csrf]')->val();
    }

    /**
     * @param DomNode $filePageDom
     *
     * @return string
     */
    private function fetchFileLinkCode(DomNode $filePageDom)
    {
        return $filePageDom->query('#DownloadForm_linkcode')->val();
    }

    /**
     * @param string $fileUrl
     *
     * @return ResponseInterface
     */
    private function requestFilePage($fileUrl)
    {
        $request = $this->requestDecorator->decorate(new Request('GET', $fileUrl));

        return $this->httpClient->send($request, ['allow_redirects' => false]);
    }

    /**
     * @param ResponseInterface $response
     * @return DomNode
     */
    private function parseFilePageDom(ResponseInterface $response)
    {
        $html = $response->getBody()->getContents();

        return pQuery::parseStr($html);
    }

    /**
     * @param ResponseInterface $filePageResponse
     *
     * @return bool
     */
    private function isDirectDownload(ResponseInterface $filePageResponse)
    {
        return in_array($filePageResponse->getStatusCode(), [301, 302])
            && $filePageResponse->hasHeader('location')
            && preg_match('#^https?://download#', $filePageResponse->getHeaderLine('location'));
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
