<?php

namespace Magpie\Queues\Concepts;

use Magpie\General\Concepts\Identifiable;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Concepts\Runnable;

/**
 * An executable unit (dequeued) from queue
 */
interface QueueExecutable extends Runnable, Releasable, Identifiable
{
    /**
     * @inheritDoc
     */
    public function getId() : string|int;


    /**
     * Name of the executable unit
     * @return string
     */
    public function getName() : string;
}