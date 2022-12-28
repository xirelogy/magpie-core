<?php

namespace Magpie\HttpServer\Exceptions;

use Exception;
use Throwable;

/**
 * An exception that is expected to be treated as a HTTP response
 */
abstract class HttpResponseException extends Exception
{
    /**
     * Constructor
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    /**
     * Extra HTTP headers to be provided along with the response
     * @return iterable<string, mixed>
     */
    public function getHeaders() : iterable
    {
        return [];
    }
}