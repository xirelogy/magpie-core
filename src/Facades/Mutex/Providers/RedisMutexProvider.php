<?php

namespace Magpie\Facades\Mutex\Providers;

use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\Facades\Mutex\MutexHandle;
use Magpie\Facades\Random;
use Magpie\Facades\Redis\RedisClient;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Stopwatch;
use Magpie\General\Randoms\RandomCharset;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Kernel\Kernel;

/**
 * Mutex provider using redis
 */
class RedisMutexProvider implements MutexProvidable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'redis';
    /**
     * Pseudo class name
     */
    protected const PSEUDO_CLASSNAME = '::::mutex';

    /**
     * @var RedisClient Associated redis client
     */
    protected readonly RedisClient $redis;


    /**
     * Constructor
     * @param RedisClient $redis
     */
    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
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
    public function acquire(string $key, Duration $ttl, ?Duration $timeout = null) : bool
    {
        $nonce = Random::string(32, RandomCharset::LOWER_ALPHANUM);

        $redisKey = RedisClient::makeRedisKey(static::PSEUDO_CLASSNAME, $key);

        $sw = Stopwatch::create();
        while (true) {
            // Try to set
            try {
                $isSet = $this->redis->setWithOptions($redisKey, $nonce)
                    ->withTtl($ttl)
                    ->ifNotYetExist()
                    ->go();
                if ($isSet) return true;
            } catch (OperationFailedException $ex) {
                throw $ex;
            } catch (Exception $ex) {
                throw new OperationFailedException(previous: $ex);
            }

            // Check timeout
            if ($sw->isTimeout($timeout)) return false;

            // Pause before next attempt
            MutexHandle::sleep();
        }
    }


    /**
     * @inheritDoc
     */
    public function release(string $key) : void
    {
        $redisKey = RedisClient::makeRedisKey(static::PSEUDO_CLASSNAME, $key);

        Excepts::noThrow(fn () => $this->redis->delete($redisKey));
    }


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(MutexProvidable::class, $this);
    }
}
