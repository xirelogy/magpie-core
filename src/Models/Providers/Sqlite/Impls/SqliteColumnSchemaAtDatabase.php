<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteColumnDefinition;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteNotNullColumnDefinitionConstraint;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqlitePrimaryKeyColumnDefinitionConstraint;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteUniqueColumnDefinitionConstraint;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;

/**
 * SQLite column schema at database level
 * @internal
 */
class SqliteColumnSchemaAtDatabase extends ColumnSchemaAtDatabase
{
    /**
     * @var SqliteColumnDefinition Associated definition
     */
    protected readonly SqliteColumnDefinition $def;
    /**
     * @var bool If non null
     */
    protected bool $isNonNull = false;
    /**
     * @var bool If primary key
     */
    protected bool $isPrimaryKey = false;
    /**
     * @var bool If unique
     */
    protected bool $isUnique = false;
    /**
     * @var bool If auto increment
     */
    protected bool $isAutoIncrement = false;


    /**
     * Constructor
     * @param SqliteConnection $connection
     * @param SqliteColumnDefinition $def
     * @param array $record
     */
    public function __construct(SqliteConnection $connection, SqliteColumnDefinition $def, array $record)
    {
        _used($connection, $record);

        $this->def = $def;

        foreach ($def->constraints as $constraint) {
            if ($constraint instanceof SqlitePrimaryKeyColumnDefinitionConstraint) {
                $this->isPrimaryKey = true;
                $this->isAutoIncrement = $constraint->isAutoIncrement;
            } else if ($constraint instanceof SqliteUniqueColumnDefinitionConstraint) {
                $this->isUnique = true;
            } else if ($constraint instanceof SqliteNotNullColumnDefinitionConstraint) {
                $this->isNonNull = true;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->def->columnName;
    }


    /**
     * @inheritDoc
     */
    public function getDefinitionType() : string
    {
        return strtolower($this->def->typeName->__toString());
    }


    /**
     * @inheritDoc
     */
    public function isNonNull() : bool
    {
        return $this->isNonNull;
    }


    /**
     * @inheritDoc
     */
    public function isPrimaryKey() : bool
    {
        return $this->isPrimaryKey;
    }


    /**
     * @inheritDoc
     */
    public function isUnique() : bool
    {
        return $this->isUnique;
    }


    /**
     * @inheritDoc
     */
    public function isAutoIncrement() : bool
    {
        return $this->isAutoIncrement;
    }
}