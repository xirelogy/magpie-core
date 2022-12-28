<?php

namespace Magpie\Models\Exceptions;

use Throwable;

/**
 * Exception due to model cannot be identified in database (probably missing primary key)
 */
class ModelCannotBeIdentifiedException extends ModelSafetyException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Model cannot be identified in database');

        parent::__construct($message, $previous);
    }
}