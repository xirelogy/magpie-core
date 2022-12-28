<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/404 Not found
 */
class HttpNotFoundException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::NOT_FOUND;


    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Not found');

        parent::__construct($message, static::CODE, $previous);
    }
}