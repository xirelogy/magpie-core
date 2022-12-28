<?php

namespace Magpie\Codecs\Parsers\Exceptions;

use Magpie\Exceptions\ParseFailedException;
use Throwable;

/**
 * Exception due to not a number
 */
class NotNumberParseFailedException extends ParseFailedException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Not a number');

        parent::__construct($message, $previous);
    }
}