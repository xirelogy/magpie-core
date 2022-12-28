<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;
use Stringable;

/**
 * Cast for string values
 */
class StringAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : string
    {
        if (is_string($value)) return $value;
        if ($value instanceof Stringable) return $value->__toString();

        if (is_numeric($value)) return "$value";
        if (is_bool($value)) return $value ? 'true' : 'false';

        if (is_scalar($value)) return "$value";

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : string
    {
        if (is_string($value)) return $value;
        if ($value instanceof Stringable) return $value->__toString();

        if (is_numeric($value)) return "$value";
        if (is_bool($value)) return $value ? 'true' : 'false';

        if (is_scalar($value)) return "$value";

        throw new UnsupportedToDbValueException($value);
    }
}