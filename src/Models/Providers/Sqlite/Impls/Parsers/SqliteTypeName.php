<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Sugars\Quote;
use Magpie\Objects\Traits\CommonObjectPackAll;
use Stringable;

/**
 * SQLite type name
 * @internal
 */
class SqliteTypeName extends SqliteParsed implements Stringable
{
    use CommonObjectPackAll;

    /**
     * @var string Type name
     */
    public readonly string $name;
    /**
     * @var array<int> Parameters
     */
    public readonly array $params;


    /**
     * Constructor
     * @param string $name
     * @param array<int> $params
     */
    protected function __construct(string $name, array $params)
    {
        parent::__construct();

        $this->name = $name;
        $this->params = $params;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $ret = $this->name;
        if (count($this->params) > 0) {
            $ret .= Quote::bracket(implode(',', $this->params));
        }

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(SqliteTokenStream $tokens) : ?static
    {
        $name = $tokens->expectName();
        $params = [];

        if ($tokens->ifOptionalToken('(')) {
            $params[] = $tokens->expectSignNumberLiteral();
            if ($tokens->ifOptionalToken(',')) {
                $params[] = $tokens->expectSignNumberLiteral();
            }
            $tokens->expectToken(')');
        }

        return new static($name, $params);
    }
}