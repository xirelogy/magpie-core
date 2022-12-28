<?php

namespace Magpie\Codecs\Parsers;

use BackedEnum;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * Enumeration parser
 * @template E
 * @extends CreatableParser<E>
 */
abstract class EnumParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected final function onParse(mixed $value, ?string $hintName) : BackedEnum
    {
        $enumClass = static::getEnumClassName();
        if (!is_subclass_of($enumClass, BackedEnum::class)) throw new ClassNotOfTypeException($enumClass, BackedEnum::class);

        if (!is_int($value)) {
            $value = StringParser::create()->parse($value, $hintName);
        }

        return $enumClass::tryFrom($value) ?? throw new UnsupportedValueException($value);
    }


    /**
     * The enumeration class
     * @return class-string<E>
     */
    protected static abstract function getEnumClassName() : string;
}