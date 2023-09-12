<?php

namespace Magpie\Models\Schemas\Checks;

use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Impls\ModelTransactionScoped;
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
        $isUseTransaction = false;
        $statements = $tableSchema->compileStatementsAtDatabase($connection, $isUseTransaction, $listener);

        // Create the transaction scope if necessary
        $scoped = $isUseTransaction ? new ModelTransactionScoped($connection) : null;

        try {
            // Execute statements
            foreach ($statements as $statement) {
                $statement->execute();
            }

            // Scope considered successful at this point
            $scoped?->succeeded();
        } finally {
            $scoped?->release();
        }
    }
}