<?php

namespace Magpie\Caches\Providers;

use Magpie\Caches\CacheProvider;
use Magpie\Caches\Concepts\CacheFormattable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Facades\Redis\RedisClient;
use Magpie\General\DateTimes\Duration;
use Magpie\System\Kernel\Kernel;

/**
 * Cache provider based on redis
 */
class RedisCacheProvider implements CacheProvidable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'redis';

    /**
     * @var RedisClient Redis client
     */
    protected RedisClient $redis;
    /**
     * @var CacheFormattable Associated formatter
     */
    protected CacheFormattable $formatter;


    /**
     * Constructor
     * @param RedisClient $redis Redis client
     * @param CacheFormattable|null $formatter Specific formatter
     */
    public function __construct(RedisClient $redis, ?CacheFormattable $formatter = null)
    {
        $this->redis = $redis;
        $this->formatter = $formatter ?? CacheProvider::getDefaultFormatter();
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function get(string $namespace, string $key, mixed $default = null) : mixed
    {
        $redisKey = RedisClient::makeRedisKey($namespace, $key);

        $encoded = $this->redis->get($redisKey);

        if ($encoded === null) $encoded = $default;
        if ($encoded === null) return null;

        return $this->formatter->decode($encoded);
    }


    /**
     * @inheritDoc
     */
    public function set(string $namespace, string $key, mixed $value, Duration|int|null $ttl = null) : void
    {
        // Setting a 'null' is equivalent to deleting
        if ($value === null) {
            $this->delete($namespace, $key);
            return;
        }

        $redisKey = RedisClient::makeRedisKey($namespace, $key);
        $encoded = $this->formatter->encode($value);

        $this->redis->set($redisKey, $encoded, $ttl);
    }


    /**
     * @inheritDoc
     */
    public function delete(string $namespace, string $key) : void
    {
        $redisKey = RedisClient::makeRedisKey($namespace, $key);
        $this->redis->delete($redisKey);
    }


    /**
     * @inheritDoc
     */
    public function updateExpiry(string $namespace, string $key, Duration|int $ttl) : void
    {
        $redisKey = RedisClient::makeRedisKey($namespace, $key);
        $this->redis->setTtl($redisKey, $ttl);
    }


    /**
     * @inheritDoc
     */
    public function clear() : void
    {
        $this->redis->clear();
    }


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(CacheProvidable::class, $this);
    }
}