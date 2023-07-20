<?php

namespace Magpie\Codecs\Concepts;

use Magpie\Codecs\ParserHosts\ParserHost;

/**
 * General collection
 */
interface Collectable extends ParserHost
{
    /**
     * All available keys in collection
     * @return iterable<string>
     */
    public function getKeys() : iterable;


    /**
     * All key values
     * @return iterable<string, mixed>
     */
    public function all() : iterable;
}