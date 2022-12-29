<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\TransactionCompletedListenable;

/**
 * Receive transaction completion notification and defer to a closure only when successfully committed
 */
class ClosureTransactionCompletedCommittedListener implements TransactionCompletedListenable
{
    /**
     * @var Closure Deferred closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param callable():void $fn
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
        if ($isCommitted) ($this->fn)();
    }


    /**
     * Create an instance
     * @param callable():void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}