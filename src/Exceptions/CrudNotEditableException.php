<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception because object of target kind is not editable
 */
class CrudNotEditableException extends CrudException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Cannot edit object of this kind');

        parent::__construct($message, $previous);
    }
}