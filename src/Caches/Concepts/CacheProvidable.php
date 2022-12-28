<?php

namespace Magpie\Caches\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\DateTimes\Duration;
use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * Cache provider
 */
interface CacheProvidable extends DefaultProviderRegistrable
{
    /**
     * Get a value stored in cache
     * @param string $namespace Namespace where value stored
     * @param string $key Key of the value stored
     * @param mixed|null $default Default value to be returned if value is not found
     * @return mixed
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function get(string $namespace, string $key, mixed $default = null) : mixed;


    /**
     * Set a value to be stored in cache
     * @param string $namespace Namespace where value stored
     * @param string $key Key of the value stored
     * @param mixed $value Value to be stored
     * @param int|Duration|null $ttl When specified, time-to-live of the stored value in cache
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function set(string $namespace, string $key, mixed $value, int|Duration|null $ttl = null) : void;


    /**
     * Delete a value from cache
     * @param string $namespace Namespace where value stored
     * @param string $key Key of the value stored
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function delete(string $namespace, string $key) : void;


    /**
     * Update or set the expiry of the value stored
     * @param string $namespace Namespace where value stored
     * @param string $key Key of the value stored
     * @param int|Duration $ttl New time-to-live of the stored value in cache
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function updateExpiry(string $namespace, string $key, int|Duration $ttl) : void;
}