<?php

namespace Magpie\Queues\Simples;

use Throwable;

/**
 * Exception, encoded
 */
class ExceptionEncoded
{
    /**
     * @var class-string<Throwable> The exception class
     */
    public string $className;
    /**
     * @var string The exception message
     */
    public string $message;
    /**
     * @var string|null The stack trace (if any)
     */
    public ?string $trace;


    /**
     * Constructor
     * @param string $className
     * @param string $message
     * @param string|null $trace
     */
    public function __construct(string $className, string $message, ?string $trace = null)
    {
        $this->className = $className;
        $this->message = $message;
        $this->trace = $trace;
    }
}