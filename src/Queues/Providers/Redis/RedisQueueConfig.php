<?php

namespace Magpie\Queues\Providers\Redis;

use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Facades\Redis\RedisClient;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Queues\QueueConfig;

/**
 * Queue configuration for Redis based implementation
 */
#[FactoryTypeClass(RedisQueueConfig::TYPECLASS, QueueConfig::class)]
class RedisQueueConfig extends QueueConfig
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'redis';

    /**
     * @var RedisClient Associated redis client
     */
    public readonly RedisClient $redis;


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
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $redisParser = RedisClient::createEnvParser();
        $redis = $parserHost->requires($envKey->key('REDIS'), $redisParser);

        return new static($redis);
    }
}