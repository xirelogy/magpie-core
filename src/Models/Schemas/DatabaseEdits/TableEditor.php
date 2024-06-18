<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

use Exception;
use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\Models\Schemas\TableSchemaAtDatabase;

/**
 * Table editor at database
 */
abstract class TableEditor extends CommonTableEditable
{
    /**
     * @inheritDoc
     */
    public abstract function addColumn(string $name) : ColumnDatabaseEditSpecifiable;


    /**
     * Add a column from given column schema if necessary
     * @param ColumnSchema $column
     * @param TableSchemaAtDatabase $schemaAtDb
     * @param ColumnSchema|null $lastColumn
     * @param ColumnSchemaAtDatabase|null $columnAtDb
     * @return ColumnDatabaseEditSpecifiable|null
     */
    public function addCheckedColumnFromSchema(ColumnSchema $column, TableSchemaAtDatabase $schemaAtDb, ?ColumnSchema $lastColumn, ?ColumnSchemaAtDatabase &$columnAtDb = null) : ?ColumnDatabaseEditSpecifiable
    {
        $columnName = $column->getName();

        $columnAtDb = $schemaAtDb->getColumn($columnName);
        if ($columnAtDb === null) {
            // Column wasn't in database, need to add column
            $ret = $this->addColumn($columnName);
            $ret->withEditAction(AddColumnDatabaseEditAction::create());
            $ret->withColumnPositionAfter($lastColumn?->getName() ?? null);
            static::mergeColumnFromSchema($ret, $column);
            return $ret;
        } else if ($this->checkColumnNeedEdit($column, $columnAtDb)) {
            // Column in database and need editing
            $ret = $this->addColumn($columnName);
            $ret->withEditAction(ChangeColumnDatabaseEditAction::create($columnName));
            static::mergeColumnFromSchema($ret, $column);
            return $ret;
        } else {
            // Otherwise, excluded
            return null;
        }
    }


    /**
     * Compare column schema with what is available in database and decide if edit needed
     * @param ColumnSchema $column
     * @param ColumnSchemaAtDatabase $columnAtDb
     * @return bool
     */
    protected function checkColumnNeedEdit(ColumnSchema $column, ColumnSchemaAtDatabase $columnAtDb) : bool
    {
        if (!static::isColumnDefinitionTypeCompatible($column->getDefinitionType(), $columnAtDb->getDefinitionType())) return true;
        if ($column->isNonNull() != $columnAtDb->isNonNull()) return true;

        return false;
    }


    /**
     * Compare column definition types and determine if they are compatible equal
     * @param string $columnType
     * @param string $columnTypeAtDb
     * @return bool
     */
    protected static function isColumnDefinitionTypeCompatible(string $columnType, string $columnTypeAtDb) : bool
    {
        try {
            $columnTypeDef = ModelDefinition::parse(strtolower($columnType));
            $columnTypeAtDbDef = ModelDefinition::parse(strtolower($columnTypeAtDb));

            $columnType = $columnTypeDef->__toString();
            $columnTypeAtDb = $columnTypeAtDbDef->__toString();

            // Boolean is backwards compatible with all kind of 'int'
            if ($columnTypeDef->baseType === 'bool' && $columnTypeAtDbDef->baseType === 'tinyint') return true;
            if ($columnTypeDef->baseType === 'bool' && $columnTypeAtDbDef->baseType === 'smallint') return true;
            if ($columnTypeDef->baseType === 'bool' && $columnTypeAtDbDef->baseType === 'int') return true;
            if ($columnTypeDef->baseType === 'bool' && $columnTypeAtDbDef->baseType === 'bigint') return true;

            // Strings can be always safely upgraded to text
            if ($columnTypeDef->baseType === 'char' && $columnTypeAtDbDef->baseType === 'text') return true;
            if ($columnTypeDef->baseType === 'varchar' && $columnTypeAtDbDef->baseType === 'text') return true;

            // INT/FLOAT type at database allowed to be more specific
            if ($columnTypeDef->baseType === $columnTypeAtDbDef->baseType &&
                (static::isIntType($columnTypeDef->baseType) || static::isFloatType($columnTypeDef->baseType))
            ) {
                if (count($columnTypeDef->specs) < count($columnTypeAtDbDef->specs)) {
                    for ($i = 0; $i < count($columnTypeDef->specs); ++$i) {
                        if ($columnTypeDef->specs[$i] != $columnTypeAtDbDef->specs[$i]) {
                            return false;
                        }
                    }
                    return true;
                }
            }

            return $columnType === $columnTypeAtDb;
        } catch (Exception) {
            return false;
        }
    }


    /**
     * If the given base type is integer
     * @param string $baseType
     * @return bool
     */
    protected static function isIntType(string $baseType) : bool
    {
        if ($baseType === 'tinyint') return true;
        if ($baseType === 'smallint') return true;
        if ($baseType === 'int') return true;
        if ($baseType === 'bigint') return true;

        if ($baseType === 'utinyint') return true;
        if ($baseType === 'usmallint') return true;
        if ($baseType === 'uint') return true;
        if ($baseType === 'ubigint') return true;

        return false;
    }


    /**
     * If the given base type is floating point number
     * @param string $baseType
     * @return bool
     */
    protected static function isFloatType(string $baseType) : bool
    {
        if ($baseType === 'float') return true;
        if ($baseType === 'double') return true;

        return false;
    }
}