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
        $this->preventInvalidFilePage($filePageDom);

        return new DownloadableUrl(
            $this->fetchDownloadUrl($filePageDom),
            new FshareFile($fileUrl, $this->fetchFileName($filePageDom))
        );
    }

    /**
     * @inheritDoc
     */
    public function fetchFileInfo($fileUrl)
    {
        $filePageDom = $this->requestFilePageDom($fileUrl);

        return new FshareFile($fileUrl, $this->fetchFileName($filePageDom));
    }

    /**
     * Validate check throw exceptions if the page DOM is not eligible.
     *
     * @param DomNode $filePageDom
     *
     * @throws DownloadNotFoundException
     */
    private function preventInvalidFilePage(DomNode $filePageDom)
    {
        if ($filePageDom->query('#download-form')->count() < 1) {
            throw new DownloadNotFoundException('Download form not found');
        }
    }

    /**
     * @param DomNode $filePageDom
     *
     * @return string
     *
     * @throws DownloadNotFoundException
     */
    private function fetchDownloadUrl(DomNode $filePageDom)
    {
        $downloadInfo = $this->requestDownloadInfo(
            $this->fetchFileLinkCode($filePageDom),
            $this->fetchCsrfToken($filePageDom)
        );

        if (!isset($downloadInfo['url'])) {
            throw new DownloadNotFoundException('Download URL not found');
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
