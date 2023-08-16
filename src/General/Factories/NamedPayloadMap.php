<?php

namespace Magpie\General\Factories;

use BackedEnum;
use Magpie\Codecs\Formats\ClosureFormatter;
use Magpie\Codecs\Formats\Formatter;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Factories\Annotations\NamedPayload;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * A named payload map
 * @template C
 */
abstract class NamedPayloadMap
{
    /**
     * @var string|null Associated tag
     */
    protected readonly ?string $tag;
    /**
     * @var string Default payload
     */
    protected readonly string $defaultPayload;
    /**
     * @var array<string|int, string>
     */
    protected readonly array $fwdMap;
    /**
     * @var array<string, string|int>
     */
    protected readonly array $revMap;


    /**
     * Constructor
     * @param string|null $tag
     * @param string $defaultPayload
     * @throws ReflectionException
     */
    protected function __construct(?string $tag = null, string $defaultPayload = '')
    {
        $this->tag = $tag;
        $this->defaultPayload = $defaultPayload;

        $fwdMap = [];
        $revMap = [];

        $reflectClass = new ReflectionClass(static::getConstantClassName());
        foreach ($reflectClass->getReflectionConstants() as $reflectConstant) {
            $attr = static::getPayloadAttribute($reflectConstant, $this->tag);
            if ($attr === null) continue;

            /** @var string|int|BackedEnum $constantValue */
            $constantValue = $reflectConstant->getValue();
            $payloadValue = static::flatten($attr->payload);

            $fwdMap[static::flatten($constantValue)] = $payloadValue;
            $revMap[$payloadValue] = $constantValue;
        }

        $this->fwdMap = $fwdMap;
        $this->revMap = $revMap;
    }


    /**
     * Create a formatter
     * @return Formatter
     */
    public final function createFormatter() : Formatter
    {
        return ClosureFormatter::create(function (mixed $value) : string {
            $value = static::flatten($value);

            if (array_key_exists($value, $this->fwdMap)) {
                return $this->fwdMap[$value];
            }

            return $this->defaultPayload;
        });
    }


    /**
     * Create a parser
     * @return Parser<C>
     */
    public final function createParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : string|int|BackedEnum {
            $value = StringParser::create()->parse($value, $hintName);
            if (!array_key_exists($value, $this->revMap)) {
                throw new UnsupportedValueException($value);
            }

            return $this->revMap[$value];
        });
    }


    /**
     * Flatten the value
     * @param string|int|BackedEnum $value
     * @return string|int
     */
    protected static final function flatten(string|int|BackedEnum $value) : string|int
    {
        if ($value instanceof BackedEnum) return $value->value;

        return $value;
    }


    /**
     * The constant/enumeration class
     * @return class-string<C>
     */
    protected static abstract function getConstantClassName() : string;


    /**
     * Get the payload attribute with given tag
     * @param ReflectionClassConstant $reflectConstant
     * @param string|null $tag
     * @return NamedPayload|null
     */
    private static function getPayloadAttribute(ReflectionClassConstant $reflectConstant, ?string $tag) : ?NamedPayload
    {
        foreach ($reflectConstant->getAttributes(NamedPayload::class) as $reflectAttr) {
            /** @var NamedPayload $attrInstance */
            $attrInstance = $reflectAttr->newInstance();
            if ($attrInstance->tag === $tag) return $attrInstance;
        }

        return null;
    }
}