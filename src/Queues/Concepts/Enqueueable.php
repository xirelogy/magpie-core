<?php

namespace Magpie\Queues\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May add queueable 'job' to the queue
 */
interface Enqueueable
{
    /**
     * Enqueue a job to the queue
     * @param Queueable $job
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function enqueue(Queueable $job) : void;
}