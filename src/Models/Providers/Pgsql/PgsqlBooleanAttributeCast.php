<?php

namespace Magpie\Models\Providers\Pgsql;

use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Casts\BooleanAttributeCast;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for boolean values in PostgreSQL
 */
class PgsqlBooleanAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : bool
    {
        return BooleanAttributeCast::fromDb($key, $value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_integer($value)) {
            return $value !== 0;
        }

        throw new UnsupportedToDbValueException($value);
    }
}