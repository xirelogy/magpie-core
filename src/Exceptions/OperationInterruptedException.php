<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to operation interrupted
 */
class OperationInterruptedException extends OperationFailedException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Operation interrupted');

        parent::__construct($message, $previous);
    }
}