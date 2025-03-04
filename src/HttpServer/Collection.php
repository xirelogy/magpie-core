<?php

namespace Magpie\HttpServer;

use Magpie\Codecs\ParserHosts\ArrayCollection;
use Magpie\Locales\Concepts\Localizable;

/**
 * Common collection for HTTP server
 */
abstract class Collection extends ArrayCollection
{
    /**
     * Constructor
     * @param iterable<string, mixed> $keyValues
     * @param string|null $prefix
     * @param string|Localizable|null $argType
     */
    protected function __construct(iterable $keyValues, ?string $prefix = null, string|Localizable|null $argType = null)
    {
        parent::__construct(iter_flatten($keyValues), $prefix);

        if ($argType !== null) $this->argType = $argType;
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        $ret = $key;
        if (!is_empty_string($this->prefix)) $ret = $this->prefix . '.' . $key;

        return $ret;
    }
}