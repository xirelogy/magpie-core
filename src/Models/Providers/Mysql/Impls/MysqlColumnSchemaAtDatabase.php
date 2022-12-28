<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;

/**
 * MySQL column schema at database level
 * @internal
 */
class MysqlColumnSchemaAtDatabase extends ColumnSchemaAtDatabase
{
    /**
     * @var array Associated record
     */
    protected array $record;


    /**
     * Constructor
     * @param MysqlConnection $connection
     * @param array $record
     */
    public function __construct(MysqlConnection $connection, array $record)
    {
        _used($connection);
        $this->record = $record;
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->record['COLUMN_NAME'];
    }


    /**
     * @inheritDoc
     */
    public function getDefinitionType() : string
    {
        $columnType = strtolower($this->record['COLUMN_TYPE']);

        if (str_ends_with($columnType, ' unsigned')) {
            $columnType = 'u' . substr($columnType, 0, -strlen(' unsigned'));
        }

        return $columnType;
    }


    /**
     * @inheritDoc
     */
    public function isNonNull() : bool
    {
        return strtoupper($this->record['IS_NULLABLE']) === 'NO';
    }


    /**
     * @inheritDoc
     */
    public function isPrimaryKey() : bool
    {
        return strtoupper($this->record['COLUMN_KEY']) === 'PRI';
    }


    /**
     * @inheritDoc
     */
    public function isUnique() : bool
    {
        return strtoupper($this->record['COLUMN_KEY']) === 'UNI';
    }


    /**
     * @inheritDoc
     */
    public function isAutoIncrement() : bool
    {
        return strtoupper($this->record['EXTRA']) === 'AUTO_INCREMENT';
    }


    /**
     * Normalize column name
     * @param string $name
     * @return string
     */
    public static function normalizeName(string $name) : string
    {
        return strtolower($name);
    }
}