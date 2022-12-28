<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\Exceptions\UnsupportedToDbValueException;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Identifier;

/**
 * Naive identifier cast which literally does nothing
 */
abstract class IdAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static final function fromDb(string $key, mixed $value) : Identifier
    {
        if ($value instanceof Identifier) return $value;

        if (is_string($value) || is_int($value)) return static::createIdentifier($value);

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static final function toDb(string $key, mixed $value) : string|int
    {
        if (!$value instanceof Identifier) {
            if (is_string($value) || is_int($value)) {
                $value = static::acceptIdentifier($value);
            } else {
                throw new UnsupportedToDbValueException($value);
            }
        }

        return $value->getRaw();
    }


    /**
     * Create an identifier instance
     * @param string|int $rawValue
     * @return Identifier
     * @throws SafetyCommonException
     */
    protected static abstract function createIdentifier(string|int $rawValue) : Identifier;


    /**
     * Accept a display string into corresponding identifier
     * @param string|int $displayValue
     * @return Identifier
     * @throws SafetyCommonException
     */
    protected static abstract function acceptIdentifier(string|int $displayValue) : Identifier;
}