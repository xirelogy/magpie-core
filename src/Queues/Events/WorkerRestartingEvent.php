<?php

namespace Magpie\Queues\Events;

use Magpie\Events\BaseEvent;
use Magpie\General\Traits\StaticCreatable;

/**
 * Indicate that the current worker process shall be restarted
 */
class WorkerRestartingEvent extends BaseEvent
{
    use StaticCreatable;
}