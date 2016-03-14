<?php

namespace Ndthuan\FshareLib\Api;

use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\DTO\FshareFolder;

/**
 * Fshare client interface.
 */
interface FshareClientInterface
{
    /**
     * Fetch downloadable urls from Fshare public folder or file url.
     *
     * @param string $fshareUrl Eg. https://www.fshare.vn/file/YYYYYYYY/ or https://www.fshare.vn/folder/YYYYYYYY/
     *
     * @return DownloadableUrl[]
     */
    public function fetchDownloadableUrls($fshareUrl);

    /**
     * Fetch folder info from Fshare public folder url.
     *
     * @param string $folderUrl
     *
     * @return \Ndthuan\FshareLib\Api\DTO\FshareFolder
     */
    public function fetchFolderInfo($folderUrl);
}
