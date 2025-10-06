<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to object not found
 */
class ObjectNotFoundException extends ParseFailedException
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Object not found');

        parent::__construct($message, $previous);
    }
}