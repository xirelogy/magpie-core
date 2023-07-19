<?php

namespace Magpie\Codecs\Impls;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ArgumentException;
use Magpie\General\Traits\StaticClass;
use Magpie\Locales\Concepts\Localizable;

/**
 * A context with parser argument type
 * @internal
 */
class ParserArgTypeContext
{
    use StaticClass;

    /**
     * @var array<Localizable|string|null> Argument types
     */
    protected static array $argTypes = [];


    /**
     * Invoke the parser using specific argument type
     * @param Localizable|string|null $argType
     * @param Parser $parser
     * @param mixed $value
     * @param string|null $hintName
     * @return mixed
     * @throws ArgumentException
     */
    public static function parseUsingArgType(Localizable|string|null $argType, Parser $parser, mixed $value, ?string $hintName) : mixed
    {
        try {
            static::$argTypes[] = $argType;
            return $parser->parse($value, $hintName);
        } finally {
            array_pop(static::$argTypes);
        }
    }


    /**
     * The current argument type in stack
     * @return Localizable|string|null
     */
    public static function getArgType() : Localizable|string|null
    {
        $totalArgs = count(static::$argTypes);
        if ($totalArgs <= 0) return null;
        return static::$argTypes[$totalArgs - 1];
    }
}