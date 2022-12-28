<?php

namespace Magpie\Events\Concepts;

use Magpie\General\Concepts\Runnable;

/**
 * Representation of an event
 */
interface Eventable extends Runnable
{
    /**
     * Event state
     * @return mixed
     */
    public function getEventState() : mixed;
}