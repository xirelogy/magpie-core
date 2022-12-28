<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Models\ColumnName;
use Magpie\Objects\Supports\SimpleEqualQueryCondition;

/**
 * Parse for a SimpleEqualQueryCondition (for specific value)
 * @template T
 * @implements Parser<SimpleEqualQueryCondition<T>>
 */
class SimpleEqualQueryConditionParser implements Parser
{
    use CommonParser;


    /**
     * @var ColumnName|string Specific column name
     */
    protected ColumnName|string $columnName;
    /**
     * @var Parser<T> Value parser
     */
    protected Parser $valueParser;


    /**
     * Constructor
     * @param ColumnName|string $columnName
     * @param Parser<T> $valueParser
     */
    protected function __construct(ColumnName|string $columnName, Parser $valueParser)
    {
        $this->columnName = $columnName;
        $this->valueParser = $valueParser;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : SimpleEqualQueryCondition
    {
        $parsedValue = $this->valueParser->parse($value, $hintName);
        return SimpleEqualQueryCondition::for($this->columnName, $parsedValue);
    }


    /**
     * Create a new instance
     * @param ColumnName|string $columnName
     * @param Parser<T> $valueParser
     * @return static
     */
    public static function create(ColumnName|string $columnName, Parser $valueParser) : static
    {
        return new static($columnName, $valueParser);
    }
}