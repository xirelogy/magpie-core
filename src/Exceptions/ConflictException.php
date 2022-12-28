<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to conflicting situation
 */
class ConflictException extends SimplifiedCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Conflicting situation');

        parent::__construct($message, $previous);
    }
}