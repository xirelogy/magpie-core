<?php

namespace Magpie\Exceptions;

use Magpie\HttpServer\Exceptions\HttpServiceUnavailableException;
use Throwable;

/**
 * Exception due to system under maintenance
 */
class SystemUnderMaintenanceException extends HttpServiceUnavailableException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('System under maintenance');

        parent::__construct($message, $previous);
    }
}