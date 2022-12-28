<?php

namespace Magpie\Models\Exceptions;

use Throwable;

/**
 * Exception due to attempt to perform join across different connections
 */
class ModelJoinAcrossDifferentConnectionsException extends ModelSafetyException
{
    /**
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Cannot perform join across different connections');

        parent::__construct($message, $previous);
    }
}