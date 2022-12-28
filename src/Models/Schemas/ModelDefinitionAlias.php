<?php

namespace Magpie\Models\Schemas;

use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\AttributeInitializable;

/**
 * Alias for model definition
 */
class ModelDefinitionAlias
{
    /**
     * @var string The target base type being aliased
     */
    public readonly string $targetBaseType;
    /**
     * @var string New definition
     */
    public string $definition;
    /**
     * @var string Resulting native type
     */
    public string $nativeType;
    /**
     * @var class-string<AttributeCastable>|null Specific casting class
     */
    public ?string $castClass = null;
    /**
     * @var class-string<AttributeInitializable>|null Specific initializing class
     */
    public ?string $initClass = null;
    /**
     * @var class-string<AttributeInitializable>|null Specific initializing class when current field is a primary key
     */
    public ?string $primaryInitClass = null;


    /**
     * Constructor
     * @param string $targetBaseType
     * @param string $definition
     * @param string $nativeType
     * @param class-string<AttributeCastable>|null $castClass
     * @param class-string<AttributeInitializable>|null $initClass
     * @param class-string<AttributeInitializable>|null $primaryInitClass
     */
    public function __construct(string $targetBaseType, string $definition, string $nativeType, ?string $castClass = null, ?string $initClass = null, ?string $primaryInitClass = null)
    {
        $this->targetBaseType = $targetBaseType;
        $this->definition = $definition;
        $this->nativeType = $nativeType;
        $this->castClass = $castClass;
        $this->initClass = $initClass;
        $this->primaryInitClass = $primaryInitClass;
    }
}