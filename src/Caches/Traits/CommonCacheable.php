<?php

namespace Magpie\Caches\Traits;

use Magpie\Caches\CacheProvider;
use Magpie\Caches\Concepts\Cacheable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\DateTimes\Duration;

/**
 * Common implementation for Cacheable
 */
trait CommonCacheable
{
    /**
     * Key to identify in cache
     * @return string
     */
    public abstract function getCacheKey() : string;


    /**
     * Timeout before removed from cache
     * @return Duration|null
     */
    public function getCacheTimeout() : ?Duration
    {
        return null;
    }


    /**
     * Extend the current cache expiry
     * @param Duration|null $cacheTimeout
     * @return $this
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function extendCacheExpiry(?Duration $cacheTimeout = null) : static
    {
        $cacheNamespace = $this->getCurrentCacheNamespace();
        $cacheKey = $this->getCacheKey();
        $cacheTimeout = $cacheTimeout ?? $this->getCacheTimeout();

        if ($cacheTimeout !== null) {
            static::getCacheProvider()->updateExpiry($cacheNamespace, $cacheKey, $cacheTimeout);
        }

        return $this;
    }


    /**
     * Save changes
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function save() : void
    {
        $cacheNamespace = $this->getCurrentCacheNamespace();
        $cacheKey = $this->getCacheKey();
        $cacheTimeout = $this->getCacheTimeout();

        static::getCacheProvider()->set($cacheNamespace, $cacheKey, $this, $cacheTimeout);
    }


    /**
     * Delete the current item
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function delete() : void
    {
        $this->deleteFromCache();
    }


    /**
     * Delete the current item from cache
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected final function deleteFromCache() : void
    {
        $cacheNamespace = $this->getCurrentCacheNamespace();
        $cacheKey = $this->getCacheKey();

        static::getCacheProvider()->delete($cacheNamespace, $cacheKey);
    }


    /**
     * Cache namespace of current instance
     * @return string
     */
    protected function getCurrentCacheNamespace() : string
    {
        return static::getCacheNamespace();
    }


    /**
     * Create and save into cache
     * @param static $target Target to be cached
     * @param Duration|null $cacheTimeout Specific cache timeout, if not the default timeout
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @noinspection PhpDocSignatureInspection
     */
    protected static function onCacheCreate(Cacheable $target, ?Duration $cacheTimeout = null) : static
    {
        if (!$target instanceof static) throw new NotOfTypeException($target, static::class);

        $cacheNamespace = $target->getCurrentCacheNamespace();
        $cacheKey = $target->getCacheKey();
        $cacheTimeout = $cacheTimeout ?? $target->getCacheTimeout();

        static::getCacheProvider()->set($cacheNamespace, $cacheKey, $target, $cacheTimeout);

        return $target;
    }


    /**
     * Try to find a cached object with given key
     * @param string $key Cache key
     * @param string|null $cacheNamespace Specific cache namespace, if not the default resolution
     * @return static|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected static function onCacheFind(string $key, ?string $cacheNamespace = null) : ?static
    {
        $cacheNamespace = $cacheNamespace ?? static::getCacheNamespace();
        $ret = static::getCacheProvider()->get($cacheNamespace, $key);
        if (!$ret instanceof static) return null;

        return $ret;
    }


    /**
     * Associated cache service provider
     * @return CacheProvidable
     * @throws SafetyCommonException
     */
    protected static function getCacheProvider() : CacheProvidable
    {
        return CacheProvider::getDefaultProvider();
    }


    /**
     * Namespace of current cacheable
     * @return string
     */
    protected static function getCacheNamespace() : string
    {
        return static::class;
    }
}