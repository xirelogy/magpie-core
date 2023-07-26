<?php

namespace Magpie\General\Contexts;

use Closure;

/**
 * Provide support for a specific context using closure in scope
 */
class ClosureScoped extends Scoped
{
    /**
     * @var Closure Release function
     */
    protected Closure $onReleaseFn;


    /**
     * Constructor
     * @param callable():void $onSetupFn
     * @param callable():void $onReleaseFn
     */
    protected function __construct(callable $onSetupFn, callable $onReleaseFn)
    {
        $onSetupFn();
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
     * @param callable():void $onSetupFn
     * @param callable():void $onReleaseFn
     * @return static
     */
    public static function create(callable $onSetupFn, callable $onReleaseFn) : static
    {
        return new static($onSetupFn, $onReleaseFn);
    }
}
