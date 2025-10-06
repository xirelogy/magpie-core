<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\Concepts\ApiCreatable;

/**
 * Parse for an 'ApiCreatable' class name from feature matrix subject
 * @extends Parser<class-string<ApiCreatable>>
 */
class ApiCreatableFeatureParser implements Parser
{
    use CommonParser;

    /**
     * @var class-string<ApiCreatable> The base class name that the ApiCreatable should be based upon
     */
    protected readonly string $baseClassName;
    /**
     * @var string The subject type class
     */
    protected readonly string $subjectTypeClass;
    /**
     * @var class-string<ApiCreatable>|null The specific base class name that the ApiCreatable should be further checked upon
     */
    protected readonly ?string $specificBaseClassName;


    /**
     * Constructor
     * @param class-string<ApiCreatable> $baseClassName
     * @param string $subjectTypeClass
     * @param class-string<ApiCreatable>|null $specificBaseClassName
     */
    protected function __construct(string $baseClassName, string $subjectTypeClass, ?string $specificBaseClassName)
    {
        $this->baseClassName = $baseClassName;
        $this->subjectTypeClass = $subjectTypeClass;
        $this->specificBaseClassName = $specificBaseClassName;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : string
    {
        $value = StringParser::create()->parse($value, $hintName);
        $className = ClassFactory::resolveFeature($value, $this->subjectTypeClass, $this->baseClassName);

        if ($this->specificBaseClassName !== null) {
            if (!is_subclass_of($className, $this->specificBaseClassName)) throw new ClassNotOfTypeException($className, $this->specificBaseClassName);
        }

        if (!is_subclass_of($className, ApiCreatable::class)) throw new ClassNotOfTypeException($className, ApiCreatable::class);

        return $className;
    }


    /**
     * Create an instance
     * @param class-string<ApiCreatable> $baseClassName
     * @param string $subjectTypeClass
     * @param class-string<ApiCreatable>|null $specificBaseClassName
     * @return static
     */
    public static function create(string $baseClassName, string $subjectTypeClass, ?string $specificBaseClassName = null) : static
    {
        return new static($baseClassName, $subjectTypeClass, $specificBaseClassName);
    }
}