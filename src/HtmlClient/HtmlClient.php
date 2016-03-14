<?php

namespace Ndthuan\FshareLib\HtmlClient;

use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\FileFetcherInterface;
use Ndthuan\FshareLib\Api\FolderFetcherInterface;
use Ndthuan\FshareLib\Api\FshareClientInterface;

/**
 * Manipulates Fshare downloads based on HTML pages.
 */
class HtmlClient implements FshareClientInterface
{
    /**
     * @var FolderFetcherInterface
     */
    private $folderFetcher;

    /**
     * @var FileFetcherInterface
     */
    private $fileFetcher;

    /**
     * HtmlClient constructor.
     *
     * @param FolderFetcherInterface $folderFetcher
     * @param FileFetcherInterface $fileFetcher
     */
    public function __construct(
        FolderFetcherInterface $folderFetcher,
        FileFetcherInterface $fileFetcher
    ) {
        $this->folderFetcher = $folderFetcher;
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * @inheritDoc
     */
    public function fetchDownloadableUrls($fshareUrl)
    {
        if (false !== strpos($fshareUrl, '/folder/')) {
            return $this->fetchDownloadableUrlsFromFolder($fshareUrl);
        }

        return [$this->fileFetcher->fetchDownloadableUrl($fshareUrl)];
    }

    /**
     * @inheritDoc
     */
    public function fetchFolderInfo($folderUrl)
    {
        return $this->folderFetcher->fetchFolderInfo($folderUrl);
    }

    /**
     * @param string $folderUrl
     *
     * @return DownloadableUrl[]
     */
    private function fetchDownloadableUrlsFromFolder($folderUrl)
    {
        $downloadableUrls = [];

        $folderInfo = $this->folderFetcher->fetchFolderInfo($folderUrl);

        foreach ($folderInfo->getFiles() as $file) {
            $downloadableUrls[] = $this->fileFetcher->fetchDownloadableUrl($file->getUrl());
        }

        return $downloadableUrls;
    }
}
