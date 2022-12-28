<?php

namespace Magpie\Models;

use Magpie\General\Sugars\Excepts;
use Magpie\Models\Concepts\QuerySelectable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;
use Magpie\Models\Schemas\TableSchema;

/**
 * All the columns of given model
 */
class AllColumns implements QuerySelectable
{
    /**
     * @var Model|TableSchema|string The model table
     */
    public readonly Model|TableSchema|string $table;


    /**
     * Constructor
     * @param Model|TableSchema|string $table
     */
    protected function __construct(Model|TableSchema|string $table)
    {
        $this->table = static::resolveTable($table);
    }


    /**
     * @inheritDoc
     * @internal
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $sql = ColumnName::fromTable($this->table, '*')->toSql($context->tableSchema);

        if ($this->table->getName() === ($context->tableSchema?->getName() ?? '')) {
            $context->modelFinalizer?->markAllColumnsSelected();
        }

        return new QueryStatement($sql);
    }


    /**
     * Create instance
     * @param Model|TableSchema|string $table
     * @return static
     */
    public static function for(Model|TableSchema|string $table) : static
    {
        return new static($table);
    }


    /**
     * Resolve table name
     * @param Model|TableSchema|string $table
     * @return Model|TableSchema|string
     */
    protected static function resolveTable(Model|TableSchema|string $table) : Model|TableSchema|string
    {
        if ($table instanceof Model) return $table;
        if ($table instanceof TableSchema) return $table;

        if (is_subclass_of($table, Model::class)) {
            return Excepts::noThrow(fn () => TableSchema::from($table), $table);
        }

        return $table;
    }
}