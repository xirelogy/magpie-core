<?php

namespace Magpie\Codecs\Concepts;

use Magpie\Codecs\Parsers\Parser;

/**
 * An object which is parseable using relevant parser
 * @template T
 */
interface ObjectParseable
{
    /**
     * Create corresponding parser
     * @return Parser<T>
     */
    public static function createParser() : Parser;
}