<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/400 Bad request
 */
class HttpBadRequestException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::BAD_REQUEST;


    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Bad request');

        parent::__construct($message, static::CODE, $previous);
    }
}