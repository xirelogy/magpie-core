<?php

namespace Magpie\Events;

use Exception;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\Concepts\EventReceivable;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Contexts\ScopedCollection;

/**
 * Basic implementation of event receiver
 */
abstract class BaseEventReceiver implements EventReceivable
{
    /**
     * @inheritDoc
     */
    public static final function handleEvent(Eventable $event) : void
    {
        // Setup scope
        $scoped = new ScopedCollection(static::getScopedItems());

        try {
            static::onHandleEvent($event);
            $scoped->succeeded();
        } catch (Exception $ex) {
            $scoped->crash($ex);
            throw $ex;
        } finally {
            $scoped->release();
        }
    }


    /**
     * Handle an event
     * @param Eventable $event
     * @return void
     * @throws Exception
     */
    protected static abstract function onHandleEvent(Eventable $event) : void;


    /**
     * All scoped items
     * @return iterable<Scoped>
     */
    protected static function getScopedItems() : iterable
    {
        return [];
    }
}