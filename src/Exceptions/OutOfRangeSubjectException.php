<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to out of given subject's range
 */
class OutOfRangeSubjectException extends OutOfRangeException
{
    /**
     * Constructor
     * @param string $subject
     * @param Throwable|null $previous
     */
    public function __construct(string $subject, ?Throwable $previous = null)
    {
        $message = static::formatMessage($subject);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $subject
     * @return string|null
     */
    protected static function formatMessage(string $subject) : ?string
    {
        return _format_safe(_l('Out of {{0}} range'), $subject);
    }
}