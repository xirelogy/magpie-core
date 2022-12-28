<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to nothing changed during editing request (empty edit request)
 */
class NothingChangedException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Nothing changed');

        parent::__construct($message, $previous);
    }
}