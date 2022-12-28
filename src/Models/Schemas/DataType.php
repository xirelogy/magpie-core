<?php

namespace Magpie\Models\Schemas;

use Magpie\Models\Casts\DefaultAttributeCast;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\AttributeInitializable;

/**
 * Data type descriptor
 */
class DataType
{
    /**
     * @var string Base type in definition
     */
    public string $defBaseType;
    /**
     * @var string Native type as in PHP
     */
    public string $nativeType;
    /**
     * @var class-string<AttributeCastable> Specific casting class
     */
    public string $castClass;
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
     * @param string $defBaseType
     * @param string $nativeType
     * @param class-string<AttributeCastable>|null $castClass
     * @param class-string<AttributeInitializable>|null $initClass
     * @param class-string<AttributeInitializable>|null $primaryInitClass
     */
    public function __construct(string $defBaseType, string $nativeType, ?string $castClass = null, ?string $initClass = null, ?string $primaryInitClass = null)
    {
        $this->defBaseType = $defBaseType;
        $this->nativeType = $nativeType;
        $this->castClass = $castClass ?? DefaultAttributeCast::class;
        $this->initClass = $initClass;
        $this->primaryInitClass = $primaryInitClass;
    }
}