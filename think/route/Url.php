<?php

namespace think\route;

class Url
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $url;

    /**
     * Undocumented function
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }
}
