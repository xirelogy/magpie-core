<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * HTTP returned status code for HTTP client request indicates failure
 */
class HttpClientStatusFailedException extends HttpClientException
{
    /**
     * Constructor
     * @param string $returnHttpStatusCode
     * @param Throwable|null $previous
     */
    public function __construct(string $returnHttpStatusCode, ?Throwable $previous = null)
    {
        $message = static::formatMessage($returnHttpStatusCode);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param int $returnHttpStatusCode
     * @return string
     */
    protected static function formatMessage(int $returnHttpStatusCode) : string
    {
        return _format_safe(_l('HTTP/{{0}} returned from HTTP request'), $returnHttpStatusCode)
            ?? _l('Failure HTTP status code returned from HTTP request');
    }
}