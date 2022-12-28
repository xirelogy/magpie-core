<?php

namespace Magpie\Events\Concepts;

use Exception;

/**
 * Anything that can receive events temporarily
 */
interface EventTemporaryReceivable
{
    /**
     * Handle an event
     * @param Eventable $event
     * @return void
     * @throws Exception
     */
    public function handleEvent(Eventable $event) : void;
}