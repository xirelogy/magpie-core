<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\ParseFailedException;

/**
 * Strict boolean parser
 * @extends CreatableParser<bool>
 */
class BooleanParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : bool
    {
        if (is_bool($value)) return $value;

        throw new ParseFailedException(_l('Not a boolean'));
    }
}