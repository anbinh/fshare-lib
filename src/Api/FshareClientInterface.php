<?php

namespace Ndthuan\FshareLib\Api;

use Ndthuan\FshareLib\Api\DTO\DownloadableUrl;
use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\Api\DTO\FshareFolder;

/**
 * Fshare client interface.
 */
interface FshareClientInterface
{
    /**
     * Fetch downloadable urls from Fshare public folder or file URL.
     *
     * @param string $fshareUrl Eg. https://www.fshare.vn/file/YYYYYYYY/ or https://www.fshare.vn/folder/YYYYYYYY/
     *
     * @return DownloadableUrl[]
     */
    public function fetchDownloadableUrls($fshareUrl);

    /**
     * Fetch folder info from Fshare public folder URL.
     *
     * @param string $folderUrl
     *
     * @return FshareFolder
     */
    public function fetchFolderInfo($folderUrl);

    /**
     * Fetch file info from Fshare public file URL.
     *
     * @param string $fileUrl
     *
     * @return FshareFile
     */
    public function fetchFileInfo($fileUrl);

    /**
     * Fetch folder or file info.
     *
     * @param string $url
     *
     * @return FshareFile|FshareFolder
     */
    public function fetchFolderOrFileInfo($url);
}
