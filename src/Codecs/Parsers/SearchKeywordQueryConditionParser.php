<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ParseFailedException;
use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnExpression;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\QueryContextServiceable;
use Magpie\Models\Enums\CommonOperator;
use Magpie\Models\Query;
use Magpie\Models\QueryStatementCreator;
use Magpie\Objects\Supports\ClosureQueryCondition;
use Magpie\Objects\Supports\QueryCondition;
use Magpie\Objects\Supports\QueryOrderCondition;
use Magpie\Objects\Supports\QueryUseOrderCondition;

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
     * @var bool If sort by keyword match-ness
     */
    protected bool $isSort = false;


    /**
     * Constructor
     * @param ColumnName|string $columnName
     */
    protected function __construct(ColumnName|string $columnName)
    {
        $this->columnName = $columnName;
    }


    /**
     * Specify if sort by keyword match-ness
     * @param bool $isSort
     * @return $this
     */
    public function withSort(bool $isSort = true) : static
    {
        $this->isSort = $isSort;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : QueryCondition
    {
        $parsedValue = StringParser::create()->withTrimming()->withEmptyAsNull()->parse($value, $hintName);
        if ($parsedValue === null) throw new ParseFailedException(_l('Must not be empty'));

        $likeCondition = $this->createLikeQueryCondition($parsedValue);
        if (!$this->isSort) return $likeCondition;

        $orderCondition = $this->createSortQueryCondition($parsedValue);
        return new QueryUseOrderCondition($likeCondition, $orderCondition);
    }


    /**
     * Create like query condition
     * @param string $parsedValue
     * @return QueryCondition
     */
    protected function createLikeQueryCondition(string $parsedValue) : QueryCondition
    {
        return ClosureQueryCondition::create(function (BaseQueryConditionable $query) use ($parsedValue) : void {
            $likeValue = '%' . Query::escapeLikeString($parsedValue) . '%';
            $query->where($this->columnName, CommonOperator::LIKE, $likeValue);
        });
    }


    /**
     * Create sort query condition
     * @param string $parsedValue
     * @return QueryOrderCondition
     */
    protected function createSortQueryCondition(string $parsedValue) : QueryOrderCondition
    {
        return QueryOrderCondition::create()
            ->orderBy($this->createSortCaseQueryCondition($parsedValue))
            ->orderBy($this->createSortPositionQueryCondition($parsedValue))
            ->orderBy(ColumnExpression::function('CHAR_LENGTH', $this->columnName))
            ;
    }


    /**
     * Create sort query using case condition
     * @param string $parsedValue
     * @return ColumnExpression
     */
    protected function createSortCaseQueryCondition(string $parsedValue) : ColumnExpression
    {
        return ColumnExpression::closure(function (QueryContextServiceable $context) use ($parsedValue) : QueryStatementCreator {
            $column = $context->getColumnNameSql($this->columnName);
            $escapedValue = Query::escapeLikeString($parsedValue);

            $sql = "CASE
                WHEN $column = ? THEN 0
                WHEN $column LIKE ? THEN 1
                WHEN $column LIKE ? THEN 2
                ELSE 3
            END";

            return new QueryStatementCreator($sql, [
                $parsedValue,
                $escapedValue . '%',
                '%' . $escapedValue,
            ]);
        });
    }


    /**
     * Create sort query using position condition
     * @param string $parsedValue
     * @return ColumnExpression
     */
    protected function createSortPositionQueryCondition(string $parsedValue) : ColumnExpression
    {
        return ColumnExpression::closure(function (QueryContextServiceable $context) use ($parsedValue) : QueryStatementCreator {
            $column = $context->getColumnNameSql($this->columnName);
            return new QueryStatementCreator("POSITION(? IN $column)", [
                $parsedValue,
            ]);
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