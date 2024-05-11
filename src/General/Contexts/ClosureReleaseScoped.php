<?php

namespace Magpie\General\Contexts;

use Closure;

/**
 * Provide support for a specific context using closure in scope, but only concern in releasing
 */
class ClosureReleaseScoped extends Scoped
{
    /**
     * @var Closure Release function
     */
    protected Closure $onReleaseFn;


    /**
     * Constructor
     * @param callable():void $onReleaseFn
     */
    protected function __construct(callable $onReleaseFn)
    {
        $this->onReleaseFn = $onReleaseFn;
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        ($this->onReleaseFn)();
    }


    /**
     * Create a new instance
     * @param callable():void $onReleaseFn
     * @return static
     */
    public static function create(callable $onReleaseFn) : static
    {
        return new static($onReleaseFn);
    }
}