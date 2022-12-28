<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Representation of a general persistence exception
 */
class GeneralPersistenceException extends PersistenceException
{
    /**
     * Constructor
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?? _l('General persistence error');

        parent::__construct($message, $code, $previous);
    }
}