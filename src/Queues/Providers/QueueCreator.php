<?php

namespace Magpie\Queues\Providers;

use Exception;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Commands\CommandRegistry;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Queues\QueueConfig;
use Magpie\System\Concepts\DefaultProviderRegistrable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\Kernel;

/**
 * Queue creator
 */
abstract class QueueCreator implements DefaultProviderRegistrable, SystemBootable
{
    /**
     * Get cache provider interface (for global data exchange)
     * @return CacheProvidable
     * @deprecated
     */
    public abstract function getCacheProvider() : CacheProvidable;


    /**
     * Get queue with given name
     * @param string|null $name
     * @return Queue
     */
    public abstract function getQueue(?string $name) : Queue;


    /**
     * @inheritDoc
     */
    public final function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(self::class, $this);
    }


    /**
     * Create from configuration
     * @param QueueConfig $config
     * @return static
     * @throws Exception
     */
    public static function fromConfig(QueueConfig $config) : static
    {
        $typeClassName = ClassFactory::resolve($config->getTypeClass(), self::class);
        if (!is_subclass_of($typeClassName, self::class)) throw new ClassNotOfTypeException($typeClassName, self::class);

        return $typeClassName::specificFromConfig($config);
    }


    /**
     * Create from configuration for specific type of implementation
     * @param QueueConfig $config
     * @return static
     * @throws Exception
     */
    protected static abstract function specificFromConfig(QueueConfig $config) : static;


    /**
     * Get current instance
     * @return static
     * @throws SafetyCommonException
     */
    public static function instance() : static
    {
        /** @var static|null $instance */
        $instance = Kernel::current()->getProvider(self::class);
        if (!$instance instanceof self) throw new NotOfTypeException($instance, self::class);

        return $instance;
    }


    /**
     * @inheritDoc
     */
    public static final function systemBoot(BootContext $context) : void
    {
        CommandRegistry::includeDirectory(__DIR__ . '/../Commands');

        static::onSystemBoot($context);
    }


    /**
     * Specific system boot-up
     * @param BootContext $context Boot up context
     * @return void
     * @throws Exception
     */
    protected static abstract function onSystemBoot(BootContext $context) : void;
}