<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\Exceptions\MissingArgumentException;
use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Impls\SqlFormat;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Statement;

/**
 * MySQL table editor
 * @internal
 */
class MysqlTableEditor extends TableEditor
{
    /**
     * @var MysqlConnection Associated connection
     */
    protected MysqlConnection $connection;
    /**
     * @var array<MysqlColumnDatabaseEditSpecifier> Column declarations
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param MysqlConnection $connection
     * @param string $tableName
     */
    public function __construct(MysqlConnection $connection, string $tableName)
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
    public function addColumn(string $name) : ColumnDatabaseEditSpecifiable
    {
        $column = new MysqlColumnDatabaseEditSpecifier($name);
        $this->columns[] = $column;

        return $column;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function compile() : Statement
    {
        $database = $this->connection->getDatabase();

        $sql = 'ALTER TABLE ';
        if ($database !== null) $sql .= SqlFormat::backTick($database) . '.';
        $sql .= SqlFormat::backTick($this->tableName);

        $declarations = [];
        foreach ($this->columns as $column) {
            $declarations[] = $column->_compile($this->connection);
        }

        // Finalize
        if (count($declarations) <= 0) throw new MissingArgumentException('declarations');
        $sql .= ' ' . implode(', ', $declarations);
        return $this->connection->prepare($sql);
    }
}