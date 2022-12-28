<?php

namespace Magpie\Objects;

/**
 * Representation of a URI
 */
class Uri extends CommonUri
{
    /**
     * @var string|null Query string (after the '?' mark)
     */
    public ?string $query = null;


    /**
     * Get builder instance
     * @return UriBuilder
     */
    public function build() : UriBuilder
    {
        $ret = new UriBuilder($this->path);
        static::copy($this, $ret);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function getQueryString() : ?string
    {
        return $this->query;
    }


    /**
     * @inheritDoc
     */
    public function setQueryString(?string $query) : void
    {
        $this->query = $query;
    }
}