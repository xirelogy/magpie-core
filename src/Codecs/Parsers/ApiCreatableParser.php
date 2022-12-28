<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\Concepts\ApiCreatable;

/**
 * Parse for an 'ApiCreatable' class name
 * @extends Parser<class-string<ApiCreatable>>
 */
class ApiCreatableParser implements Parser
{
    use CommonParser;

    /**
     * @var class-string The base class name that the ApiCreatable should be based upon
     */
    protected readonly string $baseClassName;
    /**
     * @var string|null The specific base class name that the ApiCreatable should be further checked upon
     */
    protected readonly ?string $specificBaseClassName;


    /**
     * Constructor
     * @param class-string $baseClassName
     */
    protected function __construct(string $baseClassName, ?string $specificBaseClassName)
    {
        $this->baseClassName = $baseClassName;
        $this->specificBaseClassName = $specificBaseClassName;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : string
    {
        $value = StringParser::create()->parse($value, $hintName);
        $className = ClassFactory::resolve($value, $this->baseClassName);

        if ($this->specificBaseClassName !== null) {
            if (!is_subclass_of($className, $this->specificBaseClassName)) throw new ClassNotOfTypeException($className, $this->specificBaseClassName);
        }

        if (!is_subclass_of($className, ApiCreatable::class)) throw new ClassNotOfTypeException($className, ApiCreatable::class);

        return $className;
    }


    /**
     * Create an instance
     * @param string $baseClassName
     * @param string|null $specificBaseClassName
     * @return static
     */
    public static function create(string $baseClassName, ?string $specificBaseClassName = null) : static
    {
        return new static($baseClassName, $specificBaseClassName);
    }
}