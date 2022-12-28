<?php

namespace Magpie\HttpServer;

/**
 * Collection of request headers
 */
abstract class HeaderCollection extends Collection
{
    /**
     * @inheritDoc
     */
    protected function acceptKey(int|string $key) : string|int
    {
        return str_replace('-', '_', strtoupper($key));
    }


    /**
     * @inheritDoc
     */
    protected function formatKey(int|string $key) : string|int
    {
        return str_replace('_', '-', strtolower($key));
    }
}