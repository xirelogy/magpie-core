<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Exceptions;

use Throwable;

/**
 * General OpenSSL exception (without details)
 */
class GeneralOpenSslException extends OpenSslException
{
    /**
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? _l('OpenSSL error');

        parent::__construct($message, $previous);
    }
}