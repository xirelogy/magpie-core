<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * Exception due to encryption failure
 */
class EncryptionFailedException extends CryptoException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Encryption failed');

        parent::__construct($message, $previous, $code);
    }
}