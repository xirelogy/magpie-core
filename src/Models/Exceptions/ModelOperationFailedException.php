<?php

namespace Magpie\Models\Exceptions;

use Throwable;

/**
 * Exception due to model related operation failure
 */
class ModelOperationFailedException extends ModelSafetyException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Model operation failed');

        parent::__construct($message, $previous);
    }
}