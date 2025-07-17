<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Concepts\PrimitiveBinaryContentable;

/**
 * Parser for a file in the POST body (using form-post)
 * @extends CreatableParser<PrimitiveBinaryContentable>
 */
class PostFileParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : PrimitiveBinaryContentable
    {
        if (!$value instanceof PrimitiveBinaryContentable) throw new NotOfTypeException($value, PrimitiveBinaryContentable::class);

        return $value;
    }
}