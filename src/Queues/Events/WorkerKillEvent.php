<?php

namespace Magpie\Queues\Events;

use Magpie\Events\BaseEvent;
use Magpie\General\Traits\StaticCreatable;

/**
 * Indicate that the current worker process shall be killed
 */
class WorkerKillEvent extends BaseEvent
{
    use StaticCreatable;
}