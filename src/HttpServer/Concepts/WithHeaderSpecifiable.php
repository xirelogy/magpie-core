<?php

namespace Magpie\HttpServer\Concepts;

/**
 * HTTP header in response may be specified
 */
interface WithHeaderSpecifiable
{
    /**
     * Set header
     * @param string $headerName
     * @param string $value
     * @param bool $isAllowDuplicate
     * @return $this
     */
    public function withHeader(string $headerName, string $value, bool $isAllowDuplicate = false) : static;
}