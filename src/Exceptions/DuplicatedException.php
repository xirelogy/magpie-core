<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to duplication
 */
class DuplicatedException extends SafetyCommonException
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Duplicated');

        parent::__construct($message, $previous);
    }
}