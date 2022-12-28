<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for integer values
 */
class IntegerAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : int
    {
        if (is_integer($value)) return $value;
        if (is_float($value)) return intval(floor($value));

        if (is_string($value)) {
            if (!is_numeric($value)) throw new UnsupportedFromDbValueException($value);
            return intval($value);
        }

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : int
    {
        if (is_integer($value)) return $value;
        if (is_float($value)) return intval(floor($value));

        if (is_string($value)) {
            if (!is_numeric($value)) throw new UnsupportedToDbValueException($value);
            return intval($value);
        }

        throw new UnsupportedToDbValueException($value);
    }
}