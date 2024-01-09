<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteFailedParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;

/**
 * Column definition constraint for SQLite: 'DEFAULT'
 * @internal
 */
class SqliteDefaultColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'default';

    /**
     * @var SqliteExpression|string The default value
     */
    public readonly SqliteExpression|string $value;


    /**
     * Constructor
     * @param string|null $name
     * @param SqliteExpression|string $value
     */
    protected function __construct(?string $name, SqliteExpression|string $value)
    {
        parent::__construct($name);

        $this->value = $value;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->value = $this->value;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function onParseSpecific(?string $name, SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('DEFAULT')) return null;

        $expr = SqliteExpression::tryParse($tokens);
        if ($expr !== null) return new static($name, $expr);

        $literal = static::parseLiteral($tokens);
        if ($literal !== null) return new static($name, $literal);

        throw new SqliteFailedParserException(_l('cannot parse DEFAULT value'));
    }


    /**
     * Parse for supported literals
     * @param SqliteTokenStream $tokens
     * @return string|null
     * @throws SqliteParserException
     */
    protected static function parseLiteral(SqliteTokenStream $tokens) : ?string
    {
        $keyword = $tokens->optionalAnyKeyword(
            'NULL',
            'TRUE',
            'FALSE',
            'CURRENT_TIME',
            'CURRENT_DATE',
            'CURRENT_TIMESTAMP',
        );
        if ($keyword !== null) return $keyword;

        $plusMinus = $tokens->optionalAnyToken('+', '-');
        if ($plusMinus !== null) {
            $tokens->rewind($plusMinus);
            return '' . $tokens->expectSignNumberLiteral();
        }

        $test = $tokens->next();
        if ($test === null) return null;

        if (str_starts_with($test, '\'') && str_ends_with($test, '\'')) return $test; // string literal
        if (is_numeric($test)) return $test; // numeric-literal

        $tokens->rewind($test);
        return null;
    }
}