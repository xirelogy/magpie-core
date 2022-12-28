<?php

namespace Magpie\Models\Casts;

use DateTimeInterface;
use Magpie\Codecs\Parsers\SimpleDateParser;
use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Objects\SimpleDate;

/**
 * Cast for date values
 */
class DateAttributeCast implements AttributeCastable
{
    /**
     * Format string for interacting with database
     */
    protected const FORMAT = 'Y-m-d';


    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : SimpleDate
    {
        if ($value instanceof SimpleDate) return $value;

        if (is_string($value)) return SimpleDateParser::create()->parse($value);

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : string
    {
        if (is_string($value)) {
            $value = SimpleDateParser::create()->parse($value);
        }

        if ($value instanceof SimpleDate) {
            $value = $value->expressedInTimezone(SimpleDate::TIMEZONE);
        }

        if ($value instanceof DateTimeInterface) return $value->format(static::FORMAT);

        throw new UnsupportedToDbValueException($value);
    }
}