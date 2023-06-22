<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * General crypto exception
 */
class GeneralCryptoException extends CryptoException
{
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('General crypto exception');

        parent::__construct($message, $previous, $code);
    }
}