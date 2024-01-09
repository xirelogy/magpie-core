<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\Models\Providers\Sqlite\Exceptions\SqliteCannotParseTypeParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteFailedParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;
use Magpie\Models\Providers\Sqlite\Impls\Constants\SqliteTableOption;
use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * SQLite 'CREATE TABLE' statement
 * @internal
 */
class SqliteCreateTableStatement extends SqliteParsed
{
    use CommonObjectPackAll;

    /**
     * @var string|null Schema name
     */
    public readonly ?string $schemaName;
    /**
     * @var string Table name
     */
    public readonly string $tableName;
    /**
     * @var array<SqliteColumnDefinition> Column definitions
     */
    public readonly array $columns;
    /**
     * @var array<SqliteTableConstraint> Table constraints
     */
    public readonly array $tableConstraints;
    /**
     * @var array<string, SqliteTableOption> Table options
     */
    public readonly array $options;


    /**
     * Constructor
     * @param string|null $schemaName
     * @param string $tableName
     * @param iterable<SqliteColumnDefinition|SqliteTableConstraint> $items
     * @param iterable<string, SqliteTableOption> $options
     */
    protected function __construct(?string $schemaName, string $tableName, iterable $items, iterable $options)
    {
        parent::__construct();
        $this->schemaName = $schemaName;
        $this->tableName = $tableName;

        $retColumns = [];
        $retTableConstraints = [];
        foreach ($items as $item) {
            if ($item instanceof SqliteColumnDefinition) {
                $retColumns[] = $item;
            } else if ($item instanceof SqliteTableConstraint) {
                $retTableConstraints[] = $item;
            }
        }

        $this->columns = $retColumns;
        $this->tableConstraints = $retTableConstraints;
        $this->options = iter_flatten($options);
    }


    /**
     * If specific table option specified
     * @param SqliteTableOption $option
     * @return bool
     */
    public function hasOption(SqliteTableOption $option) : bool
    {
        return array_key_exists($option->value, $this->options);
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('CREATE')) return null;
        $tokens->ifOptionalKeyword('TEMP') || $tokens->ifOptionalKeyword('TEMPORARY');
        $tokens->expectKeyword('TABLE');

        if ($tokens->ifOptionalKeyword('IF')) {
            $tokens->expectKeyword('NOT');
            $tokens->expectKeyword('EXISTS');
        }

        $schemaName = null;
        $tableName = $tokens->expectName();

        if ($tokens->ifOptionalToken('.')) {
            $schemaName = $tableName;
            $tableName = $tokens->expectName();
        }

        // AS stmt is not supported in this version
        if ($tokens->ifOptionalKeyword('AS')) throw new SqliteFailedParserException(_l('unsupported AS-stmt'));

        $tokens->expectToken('(');
        $items = static::parseItems($tokens);
        $options = static::parseOptions($tokens);

        $tokens->optionalToken(';');
        $tokens->expectEnd();

        return new static($schemaName, $tableName, $items, $options);
    }


    /**
     * Parse items (column definitions, table constraints)
     * @param SqliteTokenStream $tokens
     * @return array<SqliteColumnDefinition|SqliteTableConstraint>
     * @throws SqliteParserException
     */
    protected static function parseItems(SqliteTokenStream $tokens) : array
    {
        $ret = [];
        $hasTableConstraint = false;

        while (true) {
            $tableConstraint = SqliteTableConstraint::tryParse($tokens);
            if ($tableConstraint !== null) {
                $hasTableConstraint = true;
                $ret[] = $tableConstraint;
            } else if (!$hasTableConstraint) {
                $ret[] = SqliteColumnDefinition::parse($tokens);
            } else {
                throw new SqliteCannotParseTypeParserException(SqliteTableConstraint::class);
            }

            if ($tokens->ifOptionalToken(')')) return $ret;
            $tokens->expectToken(',');
        }
    }


    /**
     * Parse for table option
     * @param SqliteTokenStream $tokens
     * @return array<string, SqliteTableOption>
     * @throws SqliteParserException
     */
    protected static function parseOptions(SqliteTokenStream $tokens) : array
    {
        $ret = [];
        $isAllowNull = true;

        for (;;) {
            $option = $tokens->optionalAnyKeyword(
                'STRICT',
                'WITHOUT',
            );
            if ($option === null) {
                if ($isAllowNull) return $ret;
                throw new SqliteFailedParserException('missing table option');
            }

            if ($option === 'WITHOUT') $option = $tokens->expectKeyword('ROWID');

            switch ($option) {
                case 'STRICT':
                    $ret[SqliteTableOption::STRICT->value] = SqliteTableOption::STRICT;
                    break;
                case 'ROWID':
                    $ret[SqliteTableOption::WITHOUT_ROWID->value] = SqliteTableOption::WITHOUT_ROWID;
                    break;
            }

            if (!$tokens->optionalToken(',')) return $ret;
            $isAllowNull = false;
        }
    }
}