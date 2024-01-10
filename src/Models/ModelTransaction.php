<?php

namespace Magpie\Models;

use Magpie\Exceptions\GeneralPersistenceException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\TransactionCompletedListenable;
use Magpie\Models\Impls\ModelTransactionScoped;
use Magpie\System\Kernel\ExceptionHandler;
use Throwable;

/**
 * Database transaction associated to a specific model's connection
 */
abstract class ModelTransaction
{
    use StaticClass;


    /**
     * Associated connection's name
     * @return ConnectionResolvable|string
     */
    protected abstract static function getConnection() : ConnectionResolvable|string;


    /**
     * Create a new transaction that gets accepted manually
     * @return Transaction
     * @throws SafetyCommonException
     */
    public static function createManual() : Transaction
    {
        return new Transaction(static::createConnection());
    }


    /**
     * Create a scoped object with the relevant transaction
     * @return Scoped
     */
    public static function createScoped() : Scoped
    {
        try {
            return new ModelTransactionScoped(static::createConnection());
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        }
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
     * @throws PersistenceException
     */
    public static function execute(callable $fn) : mixed
    {
        _throwable() ?? throw new GeneralPersistenceException();

        return Transaction::execute(static::createConnection(), $fn);
    }


    /**
     * Create connection
     * @return Connection
     * @throws SafetyCommonException
     */
    private static function createConnection() : Connection
    {
        return Connection::from(static::getConnection());
    }
}