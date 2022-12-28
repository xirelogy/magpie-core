<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to operation failed
 */
class OperationFailedException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Operation failed');

        parent::__construct($message, $previous);
    }
}