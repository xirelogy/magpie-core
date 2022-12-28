<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to out of allowable range
 */
class OutOfRangeException extends SafetyCommonException
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Out of range');

        parent::__construct($message, $previous);
    }
}