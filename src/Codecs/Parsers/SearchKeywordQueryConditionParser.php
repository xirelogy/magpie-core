<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ParseFailedException;
use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\CommonOperator;
use Magpie\Models\Query;
use Magpie\Objects\Supports\ClosureQueryCondition;
use Magpie\Objects\Supports\QueryCondition;

/**
 * Parse for query condition fulfilling search keyword
 *  @implements Parser<QueryCondition>
 */
class SearchKeywordQueryConditionParser implements Parser
{
    use CommonParser;


    /**
     * @var ColumnName|string Specific column name
     */
    protected ColumnName|string $columnName;


    /**
     * Constructor
     * @param ColumnName|string $columnName
     */
    protected function __construct(ColumnName|string $columnName)
    {
        $this->columnName = $columnName;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : QueryCondition
    {
        $parsedValue = StringParser::create()->withTrimming()->withEmptyAsNull()->parse($value, $hintName);
        if ($parsedValue === null) throw new ParseFailedException(_l('Must not be empty'));

        return ClosureQueryCondition::create(function (BaseQueryConditionable $query) use ($parsedValue) : void {
            $likeValue = '%' . Query::escapeLikeString($parsedValue) . '%';
            $query->where($this->columnName, CommonOperator::LIKE, $likeValue);
        });
    }


    /**
     * Create a new instance
     * @param ColumnName|string $columnName
     * @return static
     */
    public static function create(ColumnName|string $columnName) : static
    {
        return new static($columnName);
    }
}