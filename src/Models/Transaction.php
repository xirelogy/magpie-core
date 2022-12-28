<?php

namespace Magpie\Models;

use Exception;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\Models\Concepts\TransactionCompletedListenable;
use Magpie\Models\Exceptions\ModelSafetyException;
use Magpie\Models\Impls\TransactionStack;

/**
 * Database transaction
 */
class Transaction implements Releasable
{
    use ReleaseOnDestruct;

    /**
     * @var TransactionStack Associated stack
     */
    protected readonly TransactionStack $stack;
    /**
     * @var int Current index
     */
    protected readonly int $index;
    /**
     * @var bool If released
     */
    protected bool $isReleased = false;


    /**
     * Constructor
     * @param Connection $connection
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function __construct(Connection $connection)
    {
        $this->stack = TransactionStack::instanceOf($connection);
        $this->index = $this->stack->acquire();
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if ($this->isReleased) return;
        $this->isReleased = true;

        Excepts::noThrow(fn () => $this->stack->release($this->index));
    }


    /**
     * Accept the transaction
     * @return void
     */
    public function accept() : void
    {
        $this->stack->accept($this->index);
    }


    /**
     * Subscribe to receive notification on completion
     * @param TransactionCompletedListenable $listener
     * @return void
     */
    public function subscribeCompleted(TransactionCompletedListenable $listener) : void
    {
        $this->stack->subscribeCompleted($listener);
    }


    /**
     * Subscribe to receive notification on completion
     * @param Connection $connection
     * @param TransactionCompletedListenable $listener
     * @return void
     */
    public static function subscribeCompletedFor(Connection $connection, TransactionCompletedListenable $listener) : void
    {
        TransactionStack::instanceOf($connection)->subscribeCompleted($listener);
    }


    /**
     * Execute within transaction
     * @param Connection $connection
     * @param callable():mixed $fn
     * @return mixed
     * @throws ModelSafetyException
     * @throws UnsupportedException
     * @throws Exception
     */
    public static function execute(Connection $connection, callable $fn) : mixed
    {
        $instance = new static($connection);

        $ret = $fn();
        $instance->accept();

        return $ret;
    }
}