<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\Schemas\ModelDefinition;

/**
 * Listener to receive events for model schema check
 */
interface ModelCheckListenable
{
    /**
     * Notify checking on a table
     * @param string $className
     * @param string $tableName
     * @param bool $isTableExisting
     * @return void
     */
    public function notifyCheckTable(string $className, string $tableName, bool $isTableExisting) : void;


    /**
     * Notify checking on a table's column
     * @param string $className
     * @param string $tableName
     * @param string $columnName
     * @param ModelDefinition|string|null $columnDef
     * @param bool $isColumnExisting
     * @return void
     */
    public function notifyCheckColumn(string $className, string $tableName, string $columnName, ModelDefinition|string|null $columnDef, bool $isColumnExisting) : void;
}