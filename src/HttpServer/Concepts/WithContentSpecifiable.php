<?php

namespace Magpie\HttpServer\Concepts;

/**
 * Content in response may be specified
 */
interface WithContentSpecifiable
{
    /**
     * Specify content
     * @param string $content
     * @return $this
     */
    public function withContent(string $content) : static;
}