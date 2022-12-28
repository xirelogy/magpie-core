<?php

namespace Magpie\Events;

use Closure;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\Concepts\EventTemporaryReceivable;

/**
 * Closure implementation of event receiver
 */
class ClosureEventTemporaryReceiver implements EventTemporaryReceivable
{
    /**
     * @var Closure Associated closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param callable(Eventable):void $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function handleEvent(Eventable $event) : void
    {
        ($this->fn)($event);
    }


    /**
     * Create a temporary receiver with given handler function
     * @param callable(Eventable):void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}