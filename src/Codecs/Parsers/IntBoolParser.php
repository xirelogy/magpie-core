<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\ParseFailedException;

/**
 * Parser for booleans represented as an integer (0 and 1)
 * @extends CreatableParser<bool>
 */
class IntBoolParser extends CreatableParser
{
    protected function onParse(mixed $value, ?string $hintName) : bool
    {
        $value = IntegerParser::create()->parse($value, $hintName);

        if ($value === 1) return true;
        if ($value === 0) return false;

        throw new ParseFailedException(_l('Not a valid integer boolean'));
    }
}