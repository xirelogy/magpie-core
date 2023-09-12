<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;
use Magpie\Models\Concepts\StatementCompilable;
use Magpie\Models\Schemas\ColumnSchema;

/**
 * Common parts of table creator/editor
 */
abstract class CommonTableEditable implements StatementCompilable
{
    /**
     * @var string Target table's name
     */
    protected string $tableName;


    /**
     * Constructor
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }


    /**
     * If there is column
     * @return bool
     */
    public abstract function hasColumn() : bool;


    /**
     * Add a column
     * @param string $name
     * @return ColumnDatabaseSpecifiable
     */
    public abstract function addColumn(string $name) : ColumnDatabaseSpecifiable;


    /**
     * Merge column specification from given schema
     * @param ColumnDatabaseSpecifiable $columnSpec
     * @param ColumnSchema $column
     * @return void
     */
    protected static function mergeColumnFromSchema(ColumnDatabaseSpecifiable $columnSpec, ColumnSchema $column) : void
    {
        $columnSpec->withDefinitionType($column->getDefinitionType());
        if ($column->isNonNull()) $columnSpec->withNonNull();
        if ($column->isPrimaryKey()) $columnSpec->withPrimaryKey();
        if ($column->isUnique()) $columnSpec->withUnique();
        if ($column->isAutoIncrement()) $columnSpec->withAutoIncrement();
        if ($column->isCreateTimestamp()) $columnSpec->withCreateTimestamp();
        if ($column->isUpdateTimestamp()) $columnSpec->withUpdateTimestamp();
        $columnSpec->withDefaultValue($column->getDefaultValue());
        $columnSpec->withComments($column->getComments());
    }
}