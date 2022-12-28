<?php

namespace Magpie\Facades\Redis\Exceptions;

use Throwable;

/**
 * Exception due to redis operation failure
 */
class RedisOperationFailedException extends RedisSafetyException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Redis operation failed');

        parent::__construct($message, $previous);
    }
}