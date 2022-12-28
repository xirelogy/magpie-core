<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\ModelStorageProvidable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * Model binder
 * @internal
 */
class ModelBinder
{
    use StaticClass;


    /**
     * Initialize storage
     * @param Model $model
     * @return ModelStorageProvidable
     * @throws SafetyCommonException
     */
    public static function initializeStorage(Model $model) : ModelStorageProvidable
    {
        $tableSchema = TableSchema::from($model);

        return DefaultModelStorageProvider::initialize($tableSchema);
    }


    /**
     * Hydrate storage
     * @param TableSchema $tableSchema
     * @param array<string, mixed> $values
     * @param array<string, class-string<AttributeCastable>> $extraCasts
     * @return ModelStorageProvidable
     * @throws SafetyCommonException
     */
    public static function hydrateStorage(TableSchema $tableSchema, array $values, array $extraCasts) : ModelStorageProvidable
    {
        return DefaultModelStorageProvider::hydrate($tableSchema, $values, $extraCasts);
    }


    /**
     * Define column name
     * @param string $modelClassName
     * @param string $columnName
     * @return ColumnName|null
     * @throws SafetyCommonException
     */
    public static function defineColumnName(string $modelClassName, string $columnName) : ?ColumnName
    {
        $tableSchema = TableSchema::from($modelClassName);
        $columnSchema = $tableSchema->getColumn($columnName);
        if ($columnSchema === null) return null;

        return ColumnName::fromTable($tableSchema, $columnName);
    }
}