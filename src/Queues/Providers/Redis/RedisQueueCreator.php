<?php

namespace Magpie\Queues\Providers\Redis;

use Magpie\Exceptions\NotOfTypeException;
use Magpie\Facades\Redis\RedisClient;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\Queues\Providers\Queue;
use Magpie\Queues\Providers\QueueCreator;
use Magpie\Queues\QueueConfig;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Queue creator for Redis based implementation
 */
#[FactoryTypeClass(RedisQueueCreator::TYPECLASS, QueueCreator::class)]
class RedisQueueCreator extends QueueCreator
{
    /**
     * Current type class
     */
    public const TYPECLASS = RedisQueueConfig::TYPECLASS;

    /**
     * @var RedisClient Redis client
     */
    protected readonly RedisClient $redis;
    /**
     * @var array<string, RedisQueue> All queue instances
     */
    protected array $queues = [];


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
    public function getQueue(?string $name) : Queue
    {
        $key = $name ?? '';

        if (!array_key_exists($key, $this->queues)) {
            $this->queues[$key] = new RedisQueue($this->redis, $name);
        }

        return $this->queues[$key];
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromConfig(QueueConfig $config) : static
    {
        if (!$config instanceof RedisQueueConfig) throw new NotOfTypeException($config, RedisQueueConfig::class);

        return new static($config->redis);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(QueueCreator::class)
            ;

        return true;
    }


    /**
     * @inheritDoc
     */
    protected static function onSystemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
    }
}