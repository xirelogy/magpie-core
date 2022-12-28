<?php

namespace Magpie\HttpServer\Concepts;

/**
 * HTTP status code in response may be specified
 */
interface WithHttpStatusCodeSpecifiable
{
    /**
     * Set status code
     * @param int $httpStatusCode
     * @return $this
     */
    public function withStatusCode(int $httpStatusCode) : static;
}