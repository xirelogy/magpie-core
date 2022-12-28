<?php

namespace Magpie\Queues\Exceptions;

use Magpie\Exceptions\SimplifiedCommonException;
use Throwable;

/**
 * Exception due to maximum number of attempts exceeded
 */
class MaxAttemptsExceededException extends SimplifiedCommonException
{
    /**
     * Constructor
     * @param int $currentAttempt
     * @param int $maxAttempts
     * @param Throwable|null $previous
     */
    public function __construct(int $currentAttempt, int $maxAttempts, ?Throwable $previous = null)
    {
        $message = static::formatMessage($currentAttempt, $maxAttempts);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param int $currentAttempt
     * @param int $maxAttempts
     * @return string
     */
    protected static function formatMessage(int $currentAttempt, int $maxAttempts) : string
    {
        return _format_safe(_l('Maximum number of attempts of {{1}} exceeded'), $currentAttempt, $maxAttempts) ??
            _l('Maximum number of attempts exceeded');
    }
}