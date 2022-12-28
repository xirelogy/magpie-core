<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Concepts\TransactionCompletedListenable;

/**
 * Database transaction associated to a specific model's connection
 */
abstract class ModelTransaction
{
    use StaticClass;


    /**
     * Associated connection's name
     * @return string
     */
    protected abstract static function getConnection() : string;


    /**
     * Create a new transaction
     * @return Transaction
     * @throws SafetyCommonException
     */
    public static function create() : Transaction
    {
        return new Transaction(static::createConnection());
    }


    /**
     * Subscribe to receive notification on completion
     * @param TransactionCompletedListenable $listener
     * @return void
     * @throws SafetyCommonException
     */
    public static function subscribeCompleted(TransactionCompletedListenable $listener) : void
    {
        Transaction::subscribeCompletedFor(static::createConnection(), $listener);
    }


    /**
     * Execute within transaction
     * @param callable():mixed $fn
     * @return mixed
     * @throws SafetyCommonException
     */
    public static function execute(callable $fn) : mixed
    {
        return Transaction::execute(static::createConnection(), $fn);
    }


    /**
     * Create connection
     * @return Connection
     * @throws SafetyCommonException
     */
    private static function createConnection() : Connection
    {
        return Connection::fromName(static::getConnection());
    }
}