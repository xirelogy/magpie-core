<?php

namespace Magpie\Models\Exceptions;

use Magpie\Exceptions\SafetyCommonException;
use Throwable;

/**
 * Exception due to model connection failure
 */
class ModelConnectionFailedException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Failed connecting to model provider');

        parent::__construct($message, $previous);
    }
}