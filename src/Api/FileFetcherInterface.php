<?php

namespace Ndthuan\FshareLib\Api;

use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\DTO\FshareFile;

/**
 * File fetcher interface.
 */
interface FileFetcherInterface
{
    /**
     * Fetch downloadable url from Fshare public file URL.
     *
     * @param string $fileUrl Eg. https://www.fshare.vn/file/YYYYYYYY/
     *
     * @return DownloadableUrl
     */
    public function fetchDownloadableUrl($fileUrl);

    /**
     * Fetch file info from Fshare public file URL.
     *
     * @param string $fileUrl
     *
     * @return FshareFile
     */
    public function fetchFileInfo($fileUrl);
}
