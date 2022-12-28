<?php

namespace Magpie\Queues\Exceptions;

use Magpie\Exceptions\OperationTimeoutException;
use Magpie\General\DateTimes\Duration;
use Throwable;

/**
 * Exception due to maximum running time for the job had exceeded
 */
class QueueExecutionTimeoutException extends OperationTimeoutException
{
    /**
     * Constructor
     * @param Duration $maxDuration
     * @param Throwable|null $previous
     */
    public function __construct(Duration $maxDuration, ?Throwable $previous = null)
    {
        $message = static::formatMessage($maxDuration);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param Duration $maxDuration
     * @return string
     */
    protected static function formatMessage(Duration $maxDuration) : string
    {
        return _format_safe(_l('Exceeded timeout of {{0}} second(s) to execute on queue'), $maxDuration->getSeconds())
            ?? _l('Exceeded timeout to execute on queue');
    }
}