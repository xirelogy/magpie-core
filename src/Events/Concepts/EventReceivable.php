<?php

namespace Magpie\Events\Concepts;

use Exception;

/**
 * Anything that can receive events
 */
interface EventReceivable
{
    /**
     * Handle an event
     * @param Eventable $event
     * @return void
     * @throws Exception
     */
    public static function handleEvent(Eventable $event) : void;
}