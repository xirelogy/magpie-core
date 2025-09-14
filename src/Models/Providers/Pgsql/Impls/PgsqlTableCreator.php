<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;
use Magpie\Models\Providers\Pgsql\Impls\Traits\PgsqlTableCreatorCompiler;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;

/**
 * PostgreSQL table creator
 * @internal
 */
class PgsqlTableCreator extends TableCreator
{
    use PgsqlTableCreatorCompiler;

    /**
     * @var PgsqlConnection Associated connection
     */
    protected PgsqlConnection $connection;
    /**
     * @var array<PgsqlColumnDatabaseSpecifier> Column declarations
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param PgsqlConnection $connection
     * @param string $tableName
     */
    public function __construct(PgsqlConnection $connection, string $tableName)
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
        $column = new PgsqlColumnDatabaseSpecifier($name);
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
        foreach ($this->compileCreateTableSql($grammar, $this->connection, $this->tableName, $this->columns) as $sql) {
            yield $this->connection->prepare($sql);
        }
    }
}