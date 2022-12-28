<?php

namespace Magpie\Models\Impls;

use Closure;

/**
 * Listener to listen for events relevant to query setup deferred to a closure
 * @internal
 */
class ClosureQuerySetupListener extends QuerySetupListener
{
    /**
     * @var Closure Deferred closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param Closure $fn
     */
    protected function __construct(Closure $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function notifyUpdateAttributes(array $attributes) : void
    {
        ($this->fn)($attributes);
    }


    /**
     * Create an instance
     * @param callable(array):void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}