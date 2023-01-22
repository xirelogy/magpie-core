<?php

namespace Magpie\Facades\Mutex\Providers;

use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\Facades\Mutex\MutexConfig;
use Magpie\Facades\Redis\RedisClient;
use Magpie\Facades\Redis\RedisClientConfig;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Mutex configuration for redis-implementation
 */
#[FactoryTypeClass(RedisMutexConfig::TYPECLASS, MutexConfig::class)]
class RedisMutexConfig extends MutexConfig
{
    /**
     * Current type class
     */
    public const TYPECLASS = RedisMutexProvider::TYPECLASS;

    /**
     * @var RedisClientConfig Redis client configuration
     */
    public readonly RedisClientConfig $config;


    /**
     * Constructor
     * @param RedisClientConfig $config
     */
    public function __construct(RedisClientConfig $config)
    {
        $this->config = $config;
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
    public function createProvider() : MutexProvidable
    {
        $redisClient = RedisClient::initialize($this->config);
        return new RedisMutexProvider($redisClient);
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $redisConfigParser = RedisClientConfig::createEnvParser();
        $redisConfig = $parserHost->optional($envKey->key('REDIS'), $redisConfigParser, '-');

        return new static($redisConfig);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(MutexConfig::class)
        ;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
    }
}