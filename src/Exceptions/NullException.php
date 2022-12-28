<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to null access
 */
class NullException extends SafetyCommonException
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Null access');

        parent::__construct($message, $previous);
    }
}