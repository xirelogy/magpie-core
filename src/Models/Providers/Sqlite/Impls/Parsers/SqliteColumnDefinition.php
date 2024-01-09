<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * Column definition for SQLite
 * @internal
 */
class SqliteColumnDefinition extends SqliteParsed
{
    use CommonObjectPackAll;

    /**
     * @var string Column name
     */
    public readonly string $columnName;
    /**
     * @var SqliteTypeName|null Type name
     */
    public readonly ?SqliteTypeName $typeName;
    /**
     * @var array<SqliteColumnDefinitionConstraint> Constraints
     */
    public readonly array $constraints;


    /**
     * Constructor
     * @param string $columnName
     * @param SqliteTypeName|null $typeName
     * @param array $constraints
     */
    protected function __construct(string $columnName, ?SqliteTypeName $typeName, array $constraints)
    {
        parent::__construct();

        $this->columnName = $columnName;
        $this->typeName = $typeName;
        $this->constraints = $constraints;
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(SqliteTokenStream $tokens) : ?static
    {
        $columnName = $tokens->expectName();
        $typeName = SqliteTypeName::tryParse($tokens);

        $constraints = [];
        for (;;) {
            $constraint = SqliteColumnDefinitionConstraint::tryParse($tokens);
            if ($constraint === null) return new static($columnName, $typeName, $constraints);

            $constraints[] = $constraint;
        }
    }
}