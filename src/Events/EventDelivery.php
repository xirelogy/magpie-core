<?php

namespace Magpie\Events;

use Exception;
use Magpie\Events\Annotations\EventSubscribe;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\Concepts\EventReceivable;
use Magpie\Events\Concepts\EventTemporaryReceivable;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\General\Arr;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\HardCore\SourceCache;
use Magpie\System\Traits\DirectoryDiscoverable;
use Magpie\System\Traits\LazyBootable;

/**
 * Deliver events to receivers
 */
class EventDelivery implements SourceCacheable
{
    use StaticClass;
    use DirectoryDiscoverable;
    use LazyBootable;

    /**
     * @var bool If already booted up
     */
    protected static bool $isBoot = false;
    /**
     * @var array<class-string<Eventable>, class-string<EventReceivable>> Defined receivers
     */
    protected static array $receiversMap = [];
    /**
     * @var array<class-string<Eventable>, EventTemporaryReceivable> Temporary receivers
     */
    protected static array $temporaryReceivers = [];


    /**
     * Subscribe to temporary event receiving
     * @param array<class-string<Eventable>>|class-string<Eventable> $eventClassNameSpec
     * @param EventTemporaryReceivable $receiver
     * @return void
     */
    public static final function subscribe(array|string $eventClassNameSpec, EventTemporaryReceivable $receiver) : void
    {
        foreach (iter_expand($eventClassNameSpec) as $eventClassName) {
            static::onSubscribe($eventClassName, $receiver);
        }
    }


    /**
     * Subscribe to a single event class name temporary event receiving
     * @param string $eventClassName
     * @param EventTemporaryReceivable $receiver
     * @return void
     */
    protected static final function onSubscribe(string $eventClassName, EventTemporaryReceivable $receiver) : void
    {
        $receivers = static::$temporaryReceivers[$eventClassName] ?? [];

        if (in_array($receiver, $receivers)) return;
        $receivers[] = $receiver;

        static::$temporaryReceivers[$eventClassName] = $receivers;
    }


    /**
     * Unsubscribe temporary event receiving
     * @param class-string<Eventable> $eventClassName
     * @param EventTemporaryReceivable $receiver
     * @return void
     */
    public static final function unsubscribe(string $eventClassName, EventTemporaryReceivable $receiver) : void
    {
        $receivers = static::$temporaryReceivers[$eventClassName] ?? [];

        $deleted = Arr::deleteByValue($receivers, $receiver);
        if ($deleted <= 0) return;

        static::$temporaryReceivers[$eventClassName] = $receivers;
    }


    /**
     * Deliver event to its receivers
     * @param Eventable $event
     * @return void
     * @throws Exception
     */
    public static final function deliver(Eventable $event) : void
    {
        static::ensureBoot();

        foreach (static::getReceivers($event) as $receiver) {
            if (!is_subclass_of($receiver, EventReceivable::class)) continue;
            $receiver::handleEvent($event);
        }

        foreach (static::getTemporaryReceivers($event) as $receiver) {
            $receiver->handleEvent($event);
        }
    }


    /**
     * List all receivers of given event
     * @param Eventable|string $eventSpec Event specification: either an event instance of the event class name
     * @return iterable<class-string<EventReceivable>>
     */
    public static final function getReceivers(Eventable|string $eventSpec) : iterable
    {
        static::ensureBoot();

        /** @var string $className */
        $className = $eventSpec instanceof Eventable ? $eventSpec::class : $eventSpec;

        yield from (static::$receiversMap[$className] ?? []);
    }


    /**
     * List all temporary receivers of given event
     * @param Eventable|string $eventSpec
     * @return iterable<EventTemporaryReceivable>
     */
    public static final function getTemporaryReceivers(Eventable|string $eventSpec) : iterable
    {
        /** @var string $className */
        $className = $eventSpec instanceof Eventable ? $eventSpec::class : $eventSpec;

        yield from (static::$temporaryReceivers[$className] ?? []);
    }


    /**
     * @inheritDoc
     */
    protected static function onBoot() : void
    {
        $cached = SourceCache::instance()->getCache(static::class);
        if ($cached !== null) {
            static::$receiversMap = $cached['receiversMap'];
            return;
        }

        $autoload = AutoloadReflection::instance();

        foreach ($autoload->expandDiscoverySourcesReflection(static::$discoverDirectories) as $class) {
            foreach ($class->getAttributes(EventSubscribe::class) as $attribute) {
                /** @var EventSubscribe $attributeInst */
                $attributeInst = $attribute->newInstance();

                if (!is_subclass_of($class->name, EventReceivable::class)) throw new ClassNotOfTypeException($class->name, EventReceivable::class);

                $eventClassName = $attributeInst->eventClassName;
                $receiverClassNames = static::$receiversMap[$eventClassName] ?? [];

                if (in_array($class->name, $receiverClassNames)) throw new DuplicatedKeyException($class->name);
                $receiverClassNames[] = $class->name;

                static::$receiversMap[$eventClassName] = $receiverClassNames;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public static function saveSourceCache() : void
    {
        static::ensureBoot();
        SourceCache::instance()->setCache(static::class, [
            'receiversMap' => static::$receiversMap,
        ]);
    }


    /**
     * @inheritDoc
     */
    public static function deleteSourceCache() : void
    {
        SourceCache::instance()->deleteCache(static::class);

        // Un-boot
        static::$isBoot = false;
        static::$receiversMap = [];
    }
}
