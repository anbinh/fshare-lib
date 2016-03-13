<?php

namespace Ndthuan\FshareLib\Api\DTO;

/**
 * Fshare file descriptor.
 */
class FshareFile
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
     * FshareFile constructor.
     *
     * @param string $url
     * @param string $name
     */
    public function __construct($url, $name)
    {
        $this->url = $url;
        $this->name = $name;
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
}
