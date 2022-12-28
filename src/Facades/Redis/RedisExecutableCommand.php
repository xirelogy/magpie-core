<?php

namespace Magpie\Facades\Redis;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Executable commands for redis
 */
abstract class RedisExecutableCommand extends RedisCommand
{
    /**
     * Execute
     * @return bool|int
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function go() : bool|int;
}