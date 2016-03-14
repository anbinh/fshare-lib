<?php

namespace Ndthuan\FshareLib\Api;

use Ndthuan\FshareLib\Api\DTO\FshareFolder;

/**
 * Folder fetcher interface.
 */
interface FolderFetcherInterface
{
    /**
     * Fetch Fshare folder info from Fshare public folder url.
     *
     * @param string $folderUrl Eg. https://www.fshare.vn/folder/XXXXXXXX/
     *
     * @return \Ndthuan\FshareLib\Api\DTO\FshareFolder
     */
    public function fetchFolderInfo($folderUrl);
}
