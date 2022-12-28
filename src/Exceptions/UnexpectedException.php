<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception because of unexpected situation
 */
class UnexpectedException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Unexpected situation');

        parent::__construct($message, $previous);
    }
}