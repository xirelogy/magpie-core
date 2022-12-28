<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Exceptions;

use Exception;

/**
 * Exception due to OpenSSL error captured from ErrorException
 */
class ErrorOpenSslException extends OpenSslException
{
    /**
     * Constructor
     * @param Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        $message = static::formatMessage($previous->getMessage());

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $previousMessage
     * @return string
     */
    protected static function formatMessage(string $previousMessage) : string
    {
        return _format_safe(_l('OpenSSL error: {{0}}'), $previousMessage)
            ?? _l('OpenSSL error');
    }
}