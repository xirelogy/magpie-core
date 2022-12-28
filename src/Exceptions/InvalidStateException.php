<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid state
 */
class InvalidStateException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Invalid state');

        parent::__construct($message, $previous);
    }
}