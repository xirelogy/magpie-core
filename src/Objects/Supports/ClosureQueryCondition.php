<?php

namespace Magpie\Objects\Supports;

use Closure;
use Magpie\Models\BaseQueryConditionable;

/**
 * Query condition applicable by closure
 */
class ClosureQueryCondition extends QueryCondition
{
    /**
     * @var Closure Associated closure
     */
    protected Closure $fn;


    /**
     * Constructor
     * @param callable(BaseQueryConditionable):void $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public final function applyOnQuery(BaseQueryConditionable $query) : void
    {
        ($this->fn)($query);
    }


    /**
     * Create a new instance
     * @param callable(BaseQueryConditionable):void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}