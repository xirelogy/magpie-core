<?php

namespace Magpie\Models\Schemas\Checks;

use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * Utility to synchronize table schema to database
 */
class TableSchemaSynchronizer extends TableSchemaChecker
{
    /**
     * @inheritDoc
     */
    public static function apply(Model $model, ?ModelCheckListenable $listener = null) : void
    {
        static::applyOn($model, static::acceptListener($listener));
    }


    /**
     * @inheritDoc
     */
    protected static function applyOn(Model $model, ModelCheckListenable $listener) : void
    {
        $tableSchema = TableSchema::fromNative($model);

        $connection = $model->connect();
        $statement = $tableSchema->compileStatementAtDatabase($connection, $listener);
        $statement?->execute();
    }
}