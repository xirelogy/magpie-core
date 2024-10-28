<?php

namespace Magpie\Codecs\Parsers;

/**
 * Parser for monetary value processed and stored as integer values in cents, i.e.
 * 1.00 is stored as 100. This is applicable for most of the currencies used in
 * the world.
 * @extends CreatableParser<int>
 */
class IntMoneyParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : int
    {
        $value = FloatParser::create()->parse($value, $hintName);

        return intval(round($value * 100));
    }
}