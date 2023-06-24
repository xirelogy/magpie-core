<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\DecryptionFailedException;
use Magpie\Cryptos\Exceptions\PasswordRequiredCryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Traits\StaticClass;
use Throwable;

/**
 * ErrorHandling for tries
 * @internal
 */
class TryErrorHandling
{
    use StaticClass;


    /**
     * Safely try to import
     * @param callable():mixed $fn
     * @return mixed
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static final function noThrow(callable $fn) : mixed
    {
        try {
            return $fn();
        } catch (PasswordRequiredCryptoException|DecryptionFailedException $ex) {
            // These two exceptions cannot be ignored
            throw $ex;
        } catch (Throwable) {
            // Ignored with default return
            return null;
        }
    }
}