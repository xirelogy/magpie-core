<?php

namespace Magpie\Codecs\Traits;

use Magpie\Exceptions\UnsupportedEnumValueException;

/**
 * Common implementation of requiredFrom() for StringBackedEnum
 * @requires \StringBackedEnum
 */
trait CommonStringBackedEnumRequiredFrom
{
    /**
     * Translates a string into the corresponding enum case, if any.
     * If there is no matching case defined, it will throw a UnsupportedEnumValueException.
     * @param string $value
     * @return static
     * @throws UnsupportedEnumValueException
     */
    public static final function requiredFrom(string $value) : static
    {
        return static::tryFrom($value) ?? throw new UnsupportedEnumValueException($value, static::class);
    }
}