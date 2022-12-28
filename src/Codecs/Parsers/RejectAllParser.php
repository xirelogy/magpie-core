<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\UnsupportedException;

/**
 * A simple parser that rejects everything
 * @template T
 * @extends CreatableParser<T>
 */
class RejectAllParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : mixed
    {
        throw new UnsupportedException();
    }
}