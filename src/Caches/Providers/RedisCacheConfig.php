<?php

namespace Magpie\Caches\Providers;

use Magpie\Caches\CacheConfig;
use Magpie\Caches\Concepts\CacheFormattable;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\Facades\Redis\RedisClient;
use Magpie\Facades\Redis\RedisClientConfig;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Cache configuration for redis-based implementation
 */
#[FactoryTypeClass(RedisCacheConfig::TYPECLASS, CacheConfig::class)]
class RedisCacheConfig extends CacheConfig
{
    /**
     * Current type class
     */
    public const TYPECLASS = RedisCacheProvider::TYPECLASS;

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
    public function createProvider(?CacheFormattable $formatter = null) : RedisCacheProvider
    {
        $redisClient = RedisClient::initialize($this->config);
        return new RedisCacheProvider($redisClient, $formatter);
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
    protected static function specificParseTypeConfig(ConfigParser $parser) : static
    {
        $key = RedisClientConfig::createConfigRedirectSetup($parser->provider)->createKey('redis', false);
        $redisConfig = $parser->get($key);

        return new static($redisConfig);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(CacheConfig::class)
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