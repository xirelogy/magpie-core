<?php

namespace Magpie\Models\Exceptions;

use Throwable;

/**
 * Exception due to query selection had been reset and the hydration result cannot be guaranteed
 */
class QuerySelectResetException extends ModelSafetyException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Query select had been reset');

        parent::__construct($message, $previous);
    }
}