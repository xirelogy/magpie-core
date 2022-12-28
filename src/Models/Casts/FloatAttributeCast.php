<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for float values
 */
class FloatAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : float
    {
        if (is_integer($value)) return $value;
        if (is_float($value)) return $value;

        if (is_string($value)) {
            if (!is_numeric($value)) throw new UnsupportedFromDbValueException($value);
            return floatval($value);
        }

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : string
    {
        if (is_integer($value)) return "$value";
        if (is_float($value)) return "$value";

        if (is_string($value)) {
            if (!is_numeric($value)) throw new UnsupportedToDbValueException($value);
            return "$value";
        }

        throw new UnsupportedToDbValueException($value);
    }
}