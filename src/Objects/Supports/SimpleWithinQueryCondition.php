<?php

namespace Magpie\Objects\Supports;

use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\CommonOperator;

/**
 * Simple query condition matching any values within
 * @template T
 */
class SimpleWithinQueryCondition extends QueryCondition
{
    /**
     * @var ColumnName|string specific query condition
     */
    protected ColumnName|string $columnName;
    /**
     * @var array<T> Values to be matched
     */
    protected array $values;


    /**
     * Constructor
     * @param ColumnName|string $columnName
     * @param iterable<T> $values
     */
    protected function __construct(ColumnName|string $columnName, iterable $values)
    {
        $this->columnName = $columnName;
        $this->values = iter_flatten($values, false);
    }


    /**
     * @inheritDoc
     */
    public function applyOnQuery(BaseQueryConditionable $query) : void
    {
        $query->where($this->columnName, CommonOperator::IN, $this->values);
    }


    /**
     * Create condition for given column and values
     * @param ColumnName|string $columnName
     * @param iterable<T> $values
     * @return static
     */
    public static function for(ColumnName|string $columnName, iterable $values) : static
    {
        return new static($columnName, $values);
    }
}