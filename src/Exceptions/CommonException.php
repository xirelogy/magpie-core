<?php

namespace Magpie\Exceptions;

use Exception;
use Throwable;

/**
 * Common exception
 */
abstract class CommonException extends Exception
{
    /**
     * Constructor
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}