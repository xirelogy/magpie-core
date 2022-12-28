<?php

namespace Magpie\Caches\Concepts;

/**
 * May format cache into appropriate format
 */
interface CacheFormattable
{
    /**
     * Encode into cache format
     * @param mixed $value
     * @return string
     */
    public function encode(mixed $value) : string;


    /**
     * Decode from cache format
     * @param string $encoded
     * @return mixed
     */
    public function decode(string $encoded) : mixed;
}