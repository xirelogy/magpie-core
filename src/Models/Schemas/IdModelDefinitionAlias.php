<?php

namespace Magpie\Models\Schemas;

use Magpie\Models\Impls\NaiveIdAttributeCast;

/**
 * Alias for 'id' model definition
 */
class IdModelDefinitionAlias extends ModelDefinitionAlias
{
    /**
     * Constructor
     * @param string $definition
     * @param string|null $nativeType
     * @param string|null $castClass
     * @param string|null $initClass
     * @param string|null $primaryInitClass
     */
    public function __construct(string $definition, ?string $nativeType = null, ?string $castClass = null, ?string $initClass = null, ?string $primaryInitClass = null)
    {
        $nativeType = $nativeType ?? 'Identifier';
        $castClass = $castClass ?? NaiveIdAttributeCast::class;

        parent::__construct('id', $definition, $nativeType, $castClass, $initClass, $primaryInitClass);
    }
}