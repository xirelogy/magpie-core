<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Providers\Sqlite\Impls\Traits\SqliteTableCreatorCompiler;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;

/**
 * SQLite table creator
 * @internal
 */
class SqliteTableCreator extends TableCreator
{
    use SqliteTableCreatorCompiler;

    /**
     * @var SqliteConnection Associated connection
     */
    protected SqliteConnection $connection;
    /**
     * @var array<SqliteColumnDatabaseSpecifier> Column declarations
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param SqliteConnection $connection
     * @param string $tableName
     */
    public function __construct(SqliteConnection $connection, string $tableName)
    {
        parent::__construct($tableName);

        $this->connection = $connection;
    }


    /**
     * @inheritDoc
     */
    public function hasColumn() : bool
    {
        return count($this->columns) > 0;
    }


    /**
     * @inheritDoc
     */
    public function addColumn(string $name) : ColumnDatabaseSpecifiable
    {
        $column = new SqliteColumnDatabaseSpecifier($name);
        $this->columns[] = $column;

        return $column;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function compile(QueryGrammar $grammar) : iterable
    {
        $sql = static::compileCreateTableSql($grammar, $this->connection, $this->tableName, $this->columns);
        yield $this->connection->prepare($sql);
    }

}