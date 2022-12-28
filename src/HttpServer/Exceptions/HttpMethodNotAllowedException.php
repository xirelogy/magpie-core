<?php

namespace Magpie\HttpServer\Exceptions;

use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpStatusCode;
use Throwable;

/**
 * Exception for HTTP/405 Method not allowed
 */
class HttpMethodNotAllowedException extends HttpResponseException
{
    /**
     * Associated HTTP response code
     */
    public const CODE = CommonHttpStatusCode::METHOD_NOT_ALLOWED;
    /**
     * @var array<string>|null Allowed methods
     */
    public readonly ?array $allowedMethods;


    /**
     * Constructor
     * @param string|null $message
     * @param array<string> $allowedMethods
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?array $allowedMethods = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('Method not allowed');

        parent::__construct($message, static::CODE, $previous);

        $this->allowedMethods = $allowedMethods;
    }


    /**
     * @inheritDoc
     */
    public function getHeaders() : iterable
    {
        yield CommonHttpHeader::ALLOW => implode(', ', $this->allowedMethods);
    }
}