<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Argument related exceptions with general message
 */
class GeneralArgumentException extends ArgumentException
{
    /**
     * Constructor
     * @param string $message
     * @param string|null $argName
     * @param Throwable|null $previous
     */
    public function __construct(string $message, ?string $argName, ?Throwable $previous = null)
    {
        parent::__construct($argName, $message, $previous);
    }
}