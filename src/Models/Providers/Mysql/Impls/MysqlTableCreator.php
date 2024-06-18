<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\General\Sugars\Excepts;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;
use Magpie\Models\Impls\SqlFormat;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;
use Magpie\Objects\NumericVersion;

/**
 * MySQL table creator
 * @internal
 */
class MysqlTableCreator extends TableCreator
{
    /**
     * @var MysqlConnection Associated connection
     */
    protected MysqlConnection $connection;
    /**
     * @var array<MysqlColumnDatabaseSpecifier> Column declarations
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
    public function addColumn(string $name) : ColumnDatabaseSpecifiable
    {
        $column = new MysqlColumnDatabaseSpecifier($name);
        $this->columns[] = $column;

        return $column;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function compile() : iterable
    {
        $database = $this->connection->getDatabase();

        $sql = 'CREATE TABLE ';
        if ($database !== null) $sql .= SqlFormat::backTick($database) . '.';
        $sql .= SqlFormat::backTick($this->tableName);

        // Setup declarations
        $primaryKeys = [];
        $uniqueKeys = [];
        $declarations = [];
        foreach ($this->columns as $column) {
            $declarations[] = $column->_compile($this->connection);
            if ($column->isPrimaryKey()) {
                $primaryKeys[] = $column->getName();
            } else if ($column->isUnique()) {
                $uniqueKeys[] = $column->getName();
            }
        }

        // Handle primary keys
        if (count($primaryKeys) > 0) {
            $outPrimaryKeys = iter_flatten(static::applyBackTicks($primaryKeys));
            $declarations[] = 'PRIMARY KEY ' . Quote::bracket(implode(', ', $outPrimaryKeys));
        }

        // Handle unique key indices
        foreach ($uniqueKeys as $uniqueKey) {
            $indexKey = $uniqueKey . '_UNIQUE';
            $declarations[] = 'UNIQUE INDEX ' . SqlFormat::backTick($indexKey) . ' ' . Quote::bracket(SqlFormat::backTick($uniqueKey) . ' ASC') . ' ' . $this->getUniqueIndexVisibleSuffix();
        }

        // Finalize
        $sql .= ' ' . Quote::bracket(implode(', ', $declarations));
        yield $this->connection->prepare($sql);
    }


    /**
     * The VISIBLE suffix for UNIQUE INDEX
     * @return string
     */
    protected function getUniqueIndexVisibleSuffix() : string
    {
        return Excepts::noThrow(function () {
            $compare = $this->connection->getServerVersion()->compare(NumericVersion::fromNumbers(8, 0));
            if ($compare === null) return '';
            if ($compare < 0) return ''; // MySQL < 8.0 must not use VISIBLE
            return 'VISIBLE';
        }, '');
    }


    /**
     * Apply back ticks
     * @param iterable<string> $values
     * @return iterable<string>
     */
    protected static function applyBackTicks(iterable $values) : iterable
    {
        foreach ($values as $value) {
            yield SqlFormat::backTick($value);
        }
    }
}