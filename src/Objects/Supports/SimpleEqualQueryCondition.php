<?php

namespace Magpie\Objects\Supports;

use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnName;

/**
 * Simple query condition matching for equality
 * @template T
 */
class SimpleEqualQueryCondition extends QueryCondition
{
    /**
     * @var ColumnName|string specific query condition
     */
    protected ColumnName|string $columnName;
    /**
     * @var T Value to be matched
     */
    protected mixed $value;


    /**
     * Constructor
     * @param ColumnName|string $columnName
     * @param T|null $value
     */
    protected function __construct(ColumnName|string $columnName, mixed $value)
    {
        $this->columnName = $columnName;
        $this->value = $value;
    }


    /**
     * @inheritDoc
     */
    public function applyOnQuery(BaseQueryConditionable $query) : void
    {
        $query->where($this->columnName, $this->value);
    }


    /**
     * Create condition for given column and value
     * @param ColumnName|string $columnName
     * @param T|null $value
     * @return static
     */
    public static function for(ColumnName|string $columnName, mixed $value) : static
    {
        return new static($columnName, $value);
    }
}