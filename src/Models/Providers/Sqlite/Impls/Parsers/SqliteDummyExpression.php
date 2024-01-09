<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * A 'dummy' expression for SQLite
 * @internal
 */
class SqliteDummyExpression extends SqliteExpression
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'dummy';

    /**
     * @var array<string> Tokens in the expression
     */
    public readonly array $tokens;


    /**
     * Constructor
     * @param array<string> $tokens
     */
    protected function __construct(array $tokens)
    {
        parent::__construct();

        $this->tokens = $tokens;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->tokens = $this->tokens;
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
    protected static function onParseSpecific(SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalToken('(')) return null;

        $ret = [ '(' ];
        $openCount = 1;

        for (;;) {
            $next = $tokens->expectNext();
            $ret[] = $next;

            if ($next === '(') {
                ++$openCount;
            } else if ($next === ')') {
                --$openCount;
                if ($openCount <= 0) return new static($ret);
            }
        }
    }
}