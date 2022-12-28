<?php

namespace Magpie\Events\Annotations;

use Attribute;
use Magpie\Events\Concepts\Eventable;

/**
 * Declare that current class subscribes as a receiver for given event class
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class EventSubscribe
{
    /**
     * @var class-string<Eventable> The event's class name
     */
    public string $eventClassName;


    /**
     * Constructor
     * @param string $eventClassName The event's class name
     */
    public function __construct(string $eventClassName)
    {
        $this->eventClassName = $eventClassName;
    }
}