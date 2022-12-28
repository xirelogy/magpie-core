<?php

namespace Magpie\Objects\Supports;

use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnName;
use Magpie\Models\Filters\Paginator;
use Magpie\Models\Query;
use Magpie\Objects\Concepts\QueryApplicable;

/**
 * Query options applicable for ModeledObject
 */
class QueryOptions implements QueryApplicable
{
    /**
     * @var bool If soft deleted items are included
     */
    public bool $isSoftDeletesIncluded = false;
    /**
     * @var Paginator|null Paginator to be used
     */
    protected ?Paginator $usePaginator = null;
    /**
     * @var array<QueryCondition> Specific conditions to be applied
     */
    protected array $useConditions = [];
    /**
     * @var QueryOrderCondition|null Specific query condition to be applied
     */
    protected ?QueryOrderCondition $useOrderCondition = null;


    /**
     * Specify that soft deleted items are to be included (or not)
     * @param bool $isIncluded
     * @return $this
     */
    public final function withSoftDeletesIncluded(bool $isIncluded = true) : static
    {
        $this->isSoftDeletesIncluded = $isIncluded;
        return $this;
    }


    /**
     * Specify additional condition to be used
     * @param QueryCondition $useCondition
     * @return $this
     */
    public final function withCondition(QueryCondition $useCondition) : static
    {
        $this->useConditions[] = $useCondition;
        return $this;
    }


    /**
     * Specify additional not condition to be used
     * @param QueryCondition $useCondition
     * @return $this
     */
    public final function withNotCondition(QueryCondition $useCondition) : static
    {
        $this->useConditions[] = new QueryNotCondition($useCondition);
        return $this;
    }


    /**
     * Specify additional condition of simple equality to be used
     * @param ColumnName|string $columnName
     * @param mixed $value
     * @return $this
     */
    public final function withSimpleEqualCondition(ColumnName|string $columnName, mixed $value) : static
    {
        $this->useConditions[] = SimpleEqualQueryCondition::for($columnName, $value);
        return $this;
    }


    /**
     * Specify additional condition of simple non-equality to be used
     * @param ColumnName|string $columnName
     * @param mixed $value
     * @return $this
     */
    public final function withSimpleNotEqualCondition(ColumnName|string $columnName, mixed $value) : static
    {
        $this->useConditions[] = new QueryNotCondition(SimpleEqualQueryCondition::for($columnName, $value));
        return $this;
    }


    /**
     * Specify additional condition defined using closure
     * @param callable(BaseQueryConditionable):void $fn
     * @return $this
     */
    public final function withClosureCondition(callable $fn) : static
    {
        $this->useConditions[] = ClosureQueryCondition::create($fn);
        return $this;
    }


    /**
     * Specify the order condition to be used
     * @param QueryOrderCondition|null $useOrderCondition
     * @return $this
     */
    public final function withOrderCondition(?QueryOrderCondition $useOrderCondition) : static
    {
        $this->useOrderCondition = $useOrderCondition;
        return $this;
    }


    /**
     * Specify the order condition to be used if not yet specified
     * @param QueryOrderCondition|null $useOrderCondition
     * @return $this
     */
    public final function withDefaultOrderCondition(?QueryOrderCondition $useOrderCondition) : static
    {
        if ($useOrderCondition !== null && $this->useOrderCondition === null) {
            $this->useOrderCondition = $useOrderCondition;
        }

        return $this;
    }


    /**
     * Specify the paginator to be used
     * @param Paginator|null $paginator
     * @return $this
     */
    public final function withPaginator(?Paginator $paginator) : static
    {
        $this->usePaginator = $paginator;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function applyOnQuery(BaseQueryConditionable $query) : void
    {
        $this->onApplyOnQuery();

        foreach ($this->useConditions as $useCondition) {
            $useCondition->applyOnQuery($query);
        }

        $this->useOrderCondition?->applyOnQuery($query);

        if ($query instanceof Query && $this->usePaginator !== null) {
            $query->filterWith($this->usePaginator);
        }
    }


    /**
     * Preparation before applying on query
     * @return void
     * @throws SafetyCommonException
     */
    protected function onApplyOnQuery() : void
    {
        _throwable(1) ?? throw new NullException();
    }


    /**
     * A default instance
     * @return static
     */
    public static function default() : static
    {
        return new static();
    }


    /**
     * An instance with given conditions
     * @param iterable<QueryCondition|Paginator> $specs
     * @return static
     */
    public static function with(iterable $specs) : static
    {
        $ret = static::default();
        foreach ($specs as $spec) {
            if ($spec instanceof Paginator) {
                $ret->withPaginator($spec);
            } else if ($spec instanceof QueryCondition) {
                $ret->withCondition($spec);
            }
        }

        return $ret;
    }
}