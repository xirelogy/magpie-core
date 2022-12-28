<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/503 Service unavailable
 */
class HttpServiceUnavailableException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::SERVICE_UNAVAILABLE;


    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Service unavailable');

        parent::__construct($message, static::CODE, $previous);
    }
}