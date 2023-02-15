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
     * @param int $code
     */
    public function __construct(string $message, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}