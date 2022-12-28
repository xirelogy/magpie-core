<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid format string (to string formatter)
 */
class InvalidFormatStringException extends StringFormatterException
{
    /**
     * Constructor
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        if ($message === '') $message = _l('Invalid format string');

        parent::__construct($message, $previous);
    }
}