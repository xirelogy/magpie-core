<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * Exception due to decryption failure
 */
class DecryptionFailedException extends CryptoException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Decryption failed');

        parent::__construct($message, $previous, $code);
    }
}