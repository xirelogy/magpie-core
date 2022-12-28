<?php

namespace Magpie\Facades\Redis;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;

abstract class RedisQueryCommand extends RedisCommand
{
    /**
     * Query for result
     * @return iterable<string>|iterable<float, string>
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function query() : iterable;
}