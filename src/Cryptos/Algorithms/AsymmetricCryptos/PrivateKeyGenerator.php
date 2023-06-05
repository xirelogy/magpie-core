<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;

/**
 * A private key generator
 */
abstract class PrivateKeyGenerator implements TypeClassable
{
    /**
     * Generate
     * @return PrivateKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function go() : PrivateKey;
}