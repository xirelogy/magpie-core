<?php

namespace Magpie\Facades\Redis\Exceptions;

use Throwable;

/**
 * Exception due to redis database selection failed
 */
class RedisSelectDatabaseFailedException extends RedisSafetyException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Cannot select redis database');

        parent::__construct($message, $previous);
    }
}