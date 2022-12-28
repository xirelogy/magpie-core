<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to stream write failure
 */
class StreamWriteFailureException extends StreamException
{
    /**
     * Constructor
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Stream write failure');

        parent::__construct($message, $code, $previous);
    }
}