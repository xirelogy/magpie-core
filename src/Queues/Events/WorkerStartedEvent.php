<?php

namespace Magpie\Queues\Events;

use Magpie\Events\BaseEvent;
use Magpie\General\Traits\StaticCreatable;

/**
 * Indicate that the current worker process is started
 */
class WorkerStartedEvent extends BaseEvent
{
    use StaticCreatable;
}