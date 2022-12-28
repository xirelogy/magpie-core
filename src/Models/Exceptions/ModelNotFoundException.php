<?php

namespace Magpie\Models\Exceptions;

use Magpie\Exceptions\SafetyCommonException;
use Throwable;

/**
 * Exception due to model not found
 */
class ModelNotFoundException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Model not found');

        parent::__construct($message, $previous);
    }
}