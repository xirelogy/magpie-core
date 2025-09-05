<?php

namespace Magpie\Models\Schemas\Configs;

use Magpie\Models\Schemas\DataType;
use Magpie\Models\Schemas\IdModelDefinitionAlias;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\Models\Schemas\ModelDefinitionAlias;

/**
 * Schema preference
 */
abstract class SchemaPreference
{
    /**
     * @var array<string, ModelDefinitionAlias>|null Cached alias definitions
     */
    private ?array $cachedAliasDefinitions = null;


    /**
     * Get aliased definition
     * @param ModelDefinition $def
     * @return ModelDefinitionAlias|null
     */
    public final function resolveAlias(ModelDefinition $def) : ?ModelDefinitionAlias
    {
        if ($this->cachedAliasDefinitions === null) {
            $this->cachedAliasDefinitions = [];
            foreach ($this->getAliasDefinitions() as $aliasDefinition) {
                $this->cachedAliasDefinitions[$aliasDefinition->targetBaseType] = $aliasDefinition;
            }
        }

        return $this->cachedAliasDefinitions[$def->baseType] ?? null;
    }


    /**
     * All alias definitions
     * @return iterable<ModelDefinitionAlias>
     */
    protected function getAliasDefinitions() : iterable
    {
        yield new IdModelDefinitionAlias('int');
    }


    /**
     * Overridden default data type
     * @param ModelDefinition $definition
     * @return DataType|null
     */
    public function getOverriddenDefaultDataType(ModelDefinition $definition) : ?DataType
    {
        _used($definition);

        return null;
    }


    /**
     * A default schema preference
     * @return static
     */
    public static function default() : self
    {
        return new class extends SchemaPreference {

        };
    }
}