<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/403 Forbidden
 */
class HttpForbiddenException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::FORBIDDEN;


    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Forbidden');

        parent::__construct($message, static::CODE, $previous);
    }
}