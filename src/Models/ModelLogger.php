<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\StatementLogListenable;

/**
 * Database logger associated to a specific model's connection
 */
abstract class ModelLogger
{
    /**
     * Associated connection's name
     * @return ConnectionResolvable|string
     */
    protected abstract static function getConnection() : ConnectionResolvable|string;


    /**
     * Subscribe to statement log
     * @param StatementLogListenable $listener
     * @return void
     * @throws SafetyCommonException
     */
    public static function subscribe(StatementLogListenable $listener) : void
    {
        static::createConnection()->subscribeStatementLog($listener);
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