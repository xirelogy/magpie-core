<?php

namespace Magpie\Facades\Redis\PhpRedis\Exceptions;

use Magpie\Facades\Redis\Exceptions\RedisSafetyException;
use Throwable;

/**
 * PHP redis specific redis exception
 */
class PhpRedisClientException extends RedisSafetyException
{
    /**
     * Constructor
     * @param string $reason
     * @param string|null $purpose
     * @param Throwable|null $previous
     */
    public function __construct(string $reason, ?string $purpose = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($reason, $purpose);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $reason
     * @param string|null $purpose
     * @return string
     */
    protected static function formatMessage(string $reason, ?string $purpose) : string
    {
        $defaultMessage = _l('Redis error');

        if (!empty($purpose)) {
            return _format_safe(_l('Redis {{1}} error: {{0}}'), $reason, $purpose) ?? $defaultMessage;
        } else {
            return _format_safe(_l('Redis error: {{0}}'), $reason) ?? $defaultMessage;
        }
    }
}