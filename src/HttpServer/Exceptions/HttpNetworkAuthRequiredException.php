<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/511 Network authentication required
 */
class HttpNetworkAuthRequiredException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::NETWORK_AUTH_REQUIRED;


    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Network authentication required');

        parent::__construct($message, static::CODE, $previous);
    }
}