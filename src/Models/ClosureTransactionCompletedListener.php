<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\TransactionCompletedListenable;

/**
 * Receive transaction completion notification and defer to a closure
 */
class ClosureTransactionCompletedListener implements TransactionCompletedListenable
{
    /**
     * @var Closure Deferred closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param callable(bool):void $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function notifyCompleted(bool $isCommitted) : void
    {
        ($this->fn)($isCommitted);
    }


    /**
     * Create an instance
     * @param callable(bool):void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}