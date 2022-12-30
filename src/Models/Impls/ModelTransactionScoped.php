<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Log;
use Magpie\General\Contexts\Scoped;
use Magpie\Models\Connection;
use Magpie\Models\Transaction;
use Throwable;

/**
 * A scoped ModelTransaction
 * @internal
 */
class ModelTransactionScoped extends Scoped
{
    /**
     * @var Transaction Associated transaction
     */
    protected readonly Transaction $transaction;


    /**
     * Constructor
     * @param Connection $connection
     * @throws SafetyCommonException
     */
    public function __construct(Connection $connection)
    {
        $this->transaction = new Transaction($connection);
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        if ($this->isSuccessful) $this->transaction->accept();
        $this->transaction->release();
    }
}