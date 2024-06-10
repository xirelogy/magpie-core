<?php

namespace Magpie\Codecs\ParserHosts;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ArgumentException;

/**
 * Parser hosts are container of multiple values that can be further obtained through specific
 * keys. They are representations of node within structural data, i.e. objects and arrays
 */
interface ParserHost
{
    /**
     * If current parser host has given key
     * @param string|int $key
     * @return bool
     */
    public function has(string|int $key) : bool;


    /**
     * A value is required (mandatory) from current parser host
     * @template T
     * @param string|int $key Key to the value
     * @param Parser<T>|null $parser If supplied, parser to parse the value
     * @return T
     * @throws ArgumentException
     */
    public function requires(string|int $key, ?Parser $parser = null) : mixed;


    /**
     * A value is optionally required from current parser host
     * @template T
     * @param string|int $key Key to the value
     * @param Parser<T>|null $parser If supplied, parser to parse the value
     * @param T|null $default Default value to be returned when the value is not available
     * @return T|null
     * @throws ArgumentException
     */
    public function optional(string|int $key, ?Parser $parser = null, mixed $default = null) : mixed;


    /**
     * A value is optionally required from current parser host.
     * Any exception will also cause the default value returned
     * @param string|int $key
     * @param Parser<T>|null $parser
     * @param T|null $default
     * @return T
     * @template T
     */
    public function safeOptional(string|int $key, ?Parser $parser = null, mixed $default = null) : mixed;


    /**
     * Full key involving all parser host structure
     * @param string|int $key
     * @return string
     */
    public function fullKey(string|int $key) : string;


    /**
     * Get the prefix for next level parser
     * @param string|int|null $key The key where the next level parser host is extended through
     * @return string|null
     */
    public function getNextPrefix(string|int|null $key) : ?string;
}