<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to stream read failure
 */
class StreamReadFailureException extends StreamException
{
    /**
     * Constructor
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Stream read failure');

        parent::__construct($message, $code, $previous);
    }
}