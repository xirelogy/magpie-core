<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Argument related exceptions
 */
abstract class ArgumentException extends SafetyCommonException
{
    /**
     * @var string|null Associated argument name
     */
    public readonly ?string $argName;


    /**
     * Constructor
     * @param string|null $argName
     * @param string $message
     * @param Throwable|null $previous
     */
    protected function __construct(?string $argName, string $message, ?Throwable $previous)
    {
        parent::__construct($message, $previous);

        $this->argName = $argName;
    }
}