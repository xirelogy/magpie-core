<?php

namespace Magpie\Facades\Mutex\Concepts;

use Magpie\Exceptions\OperationFailedException;
use Magpie\General\DateTimes\Duration;
use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * Provider for mutex
 */
interface MutexProvidable extends DefaultProviderRegistrable
{
    /**
     * Try to acquire a mutex lock from current provider
     * @param string $key Mutex key
     * @param Duration $ttl Maximum time to live (TTL) of the mutex (after acquired) before it is automatically released
     * @param Duration|null $timeout Maximum time to wait to acquire the mutex, do not wait if duration is 0, or wait forever if null
     * @return bool If acquired
     * @throws OperationFailedException
     */
    public function acquire(string $key, Duration $ttl, ?Duration $timeout = null) : bool;


    /**
     * Release mutex lock from current provider
     * @param string $key Mutex key
     * @return void
     * @throws OperationFailedException
     */
    public function release(string $key) : void;
}