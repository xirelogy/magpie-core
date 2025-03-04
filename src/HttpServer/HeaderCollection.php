<?php

namespace Magpie\HttpServer;

/**
 * Collection of request headers
 */
abstract class HeaderCollection extends Collection
{
    /**
     * Constructor
     * @param iterable<string, mixed> $keyValues
     * @param string|null $prefix
     */
    protected function __construct(iterable $keyValues, ?string $prefix = null)
    {
        parent::__construct($keyValues, $prefix, _l('header'));
    }


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