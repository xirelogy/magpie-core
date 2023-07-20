<?php

namespace Magpie\HttpServer;

use Magpie\Codecs\ParserHosts\ArrayCollection;

/**
 * Common collection for HTTP server
 */
abstract class Collection extends ArrayCollection
{
    /**
     * Constructor
     * @param iterable<string, mixed> $keyValues
     * @param string|null $prefix
     */
    protected function __construct(iterable $keyValues, ?string $prefix = null)
    {
        parent::__construct(iter_flatten($keyValues), $prefix);
    }
}