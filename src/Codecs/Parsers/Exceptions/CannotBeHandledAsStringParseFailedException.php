<?php

namespace Magpie\Codecs\Parsers\Exceptions;

use Magpie\Exceptions\ParseFailedException;
use Throwable;

/**
 * Exception due to failure to be handled like a string
 */
class CannotBeHandledAsStringParseFailedException extends ParseFailedException
{
    /**
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Cannot be handled like a string');

        parent::__construct($message, $previous);
    }
}