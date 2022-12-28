<?php

namespace Magpie\Models\Casts;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for timestamp values
 */
class TimestampAttributeCast implements AttributeCastable
{
    /**
     * Database timezone
     */
    protected const DB_TIMEZONE = 'UTC';
    /**
     * Format string for interacting with database
     */
    protected const FORMAT = 'Y-m-d H:i:s';


    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : CarbonInterface
    {
        if ($value instanceof CarbonInterface) return $value;

        if ($value instanceof DateTimeInterface) return Carbon::createFromTimestamp($value->getTimestamp());

        if (is_string($value)) return Carbon::parse($value, static::DB_TIMEZONE);

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : string
    {
        if (is_string($value)) $value = Carbon::parse($value);

        if ($value instanceof CarbonInterface) {
            return $value->toImmutable()
                ->setTimezone(static::DB_TIMEZONE)
                ->format(static::FORMAT);
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::createFromTimestamp($value->getTimestamp())
                ->setTimezone(static::DB_TIMEZONE)
                ->format(static::FORMAT);
        }

        throw new UnsupportedToDbValueException($value);
    }
}