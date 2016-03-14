<?php

namespace Ndthuan\FshareLib\Api\DTO;

/**
 * Fshare downloadable URL.
 */
class DownloadableUrl
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var FshareFile
     */
    private $file;

    /**
     * DownloadableUrl constructor.
     *
     * @param string $url
     * @param FshareFile $file
     */
    public function __construct($url, FshareFile $file)
    {
        $this->url = $url;
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return FshareFile
     */
    public function getFile()
    {
        return $this->file;
    }
}
