<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for boolean values
 */
class BooleanAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_integer($value)) {
            return $value !== 0;
        }

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_integer($value)) {
            return $value !== 0 ? 1 : 0;
        }

        throw new UnsupportedToDbValueException($value);
    }
}