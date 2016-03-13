<?php

namespace Ndthuan\FshareLib\HtmlClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Ndthuan\FshareLib\Api\FolderFetcherInterface;
use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\Api\DTO\FshareFolder;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Fetches folder info from an HTML page.
 */
class HtmlBasedFolderFetcher implements FolderFetcherInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestDecoratorInterface
     */
    private $requestDecorator;

    /**
     * HtmlFolderFetcher constructor.
     *
     * @param ClientInterface $httpClient
     * @param RequestDecoratorInterface $requestDecorator
     */
    public function __construct(ClientInterface $httpClient, RequestDecoratorInterface $requestDecorator)
    {
        $this->httpClient = $httpClient;
        $this->requestDecorator = $requestDecorator;
    }

    /**
     * @inheritDoc
     */
    public function fetchFolderInfo($folderUrl)
    {
        $request = $this->makeRequest($folderUrl);
        $responseHtml = $this->httpClient->send($request)->getBody()->getContents();
        $dom = \pQuery::parseStr($responseHtml);

        if ($dom->query('#dlnav #path')->count() < 1) {
            throw new FolderNotFoundException("Folder not found at $folderUrl");
        }

        $folderName = ltrim($dom->query('#dlnav #path')->attr('data-path'), '/ ');
        $files = [];

        foreach ($dom->query('#filelist .file_name a') as $fileLinkElement) {
            $fileUrl = $fileLinkElement->attr('href');
            $fileName = $fileLinkElement->attr('title');

            $files[] = new FshareFile($fileUrl, $fileName);
        }

        return new FshareFolder($folderUrl, $folderName, $files);
    }

    /**
     * @param string $folderUrl
     *
     * @return RequestInterface
     */
    private function makeRequest($folderUrl)
    {
        $request = new Request('GET', $folderUrl);

        return $this->requestDecorator->decorate($request);
    }
}
