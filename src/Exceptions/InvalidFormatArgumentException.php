<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid format argument
 */
class InvalidFormatArgumentException extends StringFormatException
{
    /**
     * @var mixed Payload that causes the exception
     */
    public readonly mixed $payload;


    /**
     * Constructor
     * @param mixed $payload
     * @param Throwable|null $previous
     */
    public function __construct(mixed $payload, ?Throwable $previous = null)
    {
        parent::__construct(_l('Invalid format argument'), $previous);

        $this->payload = $payload;
    }
}