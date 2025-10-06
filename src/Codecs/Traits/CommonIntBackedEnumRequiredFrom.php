<?php

namespace Magpie\Codecs\Traits;

use Magpie\Exceptions\UnsupportedEnumValueException;

/**
 * Common implementation of requiredFrom() for StringBackedEnum
 * @requires \IntBackedEnum
 */
trait CommonIntBackedEnumRequiredFrom
{
    /**
     * Translates an int into the corresponding enum case, if any.
     * If there is no matching case defined, it will throw a UnsupportedEnumValueException.
     * @param int $value
     * @return static
     * @throws UnsupportedEnumValueException
     */
    public static final function requiredFrom(int $value) : static
    {
        return static::tryFrom($value) ?? throw new UnsupportedEnumValueException($value, static::class);
    }
}