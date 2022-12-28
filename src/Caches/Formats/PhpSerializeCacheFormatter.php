<?php

namespace Magpie\Caches\Formats;

use Magpie\Caches\Concepts\CacheFormattable;

/**
 * Cache formatter using PHP serialization
 */
class PhpSerializeCacheFormatter implements CacheFormattable
{
    /**
     * @inheritDoc
     */
    public function encode(mixed $value) : string
    {
        return serialize($value);
    }


    /**
     * @inheritDoc
     */
    public function decode(string $encoded) : mixed
    {
        return unserialize($encoded);
    }
}