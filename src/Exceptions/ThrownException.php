<?php

namespace Magpie\Exceptions;

use Exception;
use Throwable;

/**
 * An exception that was previously a Throwable
 */
class ThrownException extends Exception
{
    /**
     * Constructor
     * @param Throwable $previous
     */
    public function __construct(Throwable $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }
}