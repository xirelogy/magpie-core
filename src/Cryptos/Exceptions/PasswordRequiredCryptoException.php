<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * Exception due to password required but not supplied
 */
class PasswordRequiredCryptoException extends CryptoException
{
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Password required');

        parent::__construct($message, $previous, $code);
    }
}