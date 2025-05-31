<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnName;
use Magpie\Models\Schemas\ColumnSchema;

/**
 * Service for query context
 */
interface QueryContextServiceable
{
    /**
     * Express column name in SQL
     * @param string|ColumnName $columnName
     * @return string
     */
    public function getColumnNameSql(string|ColumnName $columnName) : string;


    /**
     * Get corresponding column schema
     * @param string|ColumnName $columnName
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getColumnSchema(string|ColumnName $columnName) : ?ColumnSchema;
}