<?php

namespace Magpie\Queues\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\DateTimes\Duration;

/**
 * May accept and remove a dispatchable 'job' from the queue
 */
interface Dequeueable
{
    /**
     * Try to dequeue a single job from the queue
     * @param Duration|null $timeout Maximum time to wait, if specified
     * @return QueueExecutable|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function dequeue(?Duration $timeout = null) : ?QueueExecutable;
}