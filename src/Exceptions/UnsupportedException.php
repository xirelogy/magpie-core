<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception because of unsupported situation
 */
class UnsupportedException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Unsupported');

        parent::__construct($message, $previous);
    }
}