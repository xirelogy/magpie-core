<?php

namespace Magpie\Queues\Providers\Redis\Impls;

use Magpie\General\Traits\StaticClass;

/**
 * Keys to metadata when encoding information in queue
 * @internal
 */
class RedisQueueEncodingKeys
{
    use StaticClass;

    /**
     * Job ID
     */
    public const ID = 'id';
    /**
     * Job name
     */
    public const NAME = 'name';
    /**
     * Maximum number of attempts
     */
    public const MAX_ATTEMPTS = 'maxAttempts';
    /**
     * Current number of attempts
     */
    public const ATTEMPTS = 'attempts';
    /**
     * Retry after (in seconds)
     */
    public const RETRY_AFTER_SEC = 'retryAfterSec';
    /**
     * Maximum job running timeout (in seconds)
     */
    public const RUNNING_TIMEOUT_SEC = 'runningTimeoutSec';
    /**
     * Job target
     */
    public const TARGET = 'target';
}