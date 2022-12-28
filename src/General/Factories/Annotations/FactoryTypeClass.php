<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;
use BackedEnum;

/**
 * Declares that current `class` is associated to given base class on given type class
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class FactoryTypeClass
{
    /**
     * @var string Target type class
     */
    public string $typeClass;
    /**
     * @var string The base class' name where the type class is based on
     */
    public string $baseClassName;


    /**
     * Constructor
     * @param string|BackedEnum $typeClass Target type class
     * @param string $baseClassName The base class' name where the type class is based on
     */
    public function __construct(string|BackedEnum $typeClass, string $baseClassName)
    {
        $this->typeClass = static::acceptTypeClass($typeClass);
        $this->baseClassName = $baseClassName;
    }


    /**
     * Accept type class string
     * @param string|BackedEnum $typeClass
     * @return string
     */
    protected static function acceptTypeClass(string|BackedEnum $typeClass) : string
    {
        if ($typeClass instanceof BackedEnum) return '' . $typeClass->value;
        return $typeClass;
    }
}