<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\General\Factories\NamedStringCodec;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast to store named string values as integers
 */
class NumericNamedStringCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : string
    {
        if (is_int($value)) return NamedStringCodec::decode($value);

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : int
    {
        if (is_string($value)) return NamedStringCodec::encode($value);

        throw new UnsupportedToDbValueException($value);
    }
}