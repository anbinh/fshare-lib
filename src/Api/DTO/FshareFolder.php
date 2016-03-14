<?php

namespace Ndthuan\FshareLib\Api\DTO;

/**
 * Fshare folder descriptor.
 */
class FshareFolder
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $name;

    /**
     * @var FshareFile[]
     */
    private $files;

    /**
     * FshareFolder constructor.
     *
     * @param string $url
     * @param string $name
     * @param FshareFile[] $files
     */
    public function __construct($url, $name, array $files)
    {
        $this->url = $url;
        $this->name = $name;
        $this->files = $files;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return FshareFile[]
     */
    public function getFiles()
    {
        return $this->files;
    }
}
