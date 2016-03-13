<?php

namespace Ndthuan\FshareLib\Api;

use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;

/**
 * File fetcher interface.
 */
interface FileFetcherInterface
{
    /**
     * Fetch downloadable url from Fshare public file url.
     *
     * @param string $fileUrl Eg. https://www.fshare.vn/file/YYYYYYYY/
     *
     * @return \Ndthuan\FshareLib\Api\DTO\DownloadableUrl
     */
    public function fetchDownloadableUrl($fileUrl);
}
