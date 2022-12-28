<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception because object of target kind is not deletable
 */
class CrudNotDeletableException extends CrudException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Cannot delete object of this kind');

        parent::__construct($message, $previous);
    }
}