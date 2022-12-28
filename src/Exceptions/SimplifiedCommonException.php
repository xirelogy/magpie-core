<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Simplified common exception (exception's code is always 0)
 */
abstract class SimplifiedCommonException extends CommonException
{
    /**
     * Constructor
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }
}