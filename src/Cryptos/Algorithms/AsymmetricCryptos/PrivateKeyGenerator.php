<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKeyGenerator;
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
    public function go() : PrivateKey
    {
        $implKey = $this->getImpl()->go();
        return PrivateKey::_fromRaw($this->getTypeClass(), $implKey);
    }


    /**
     * Get implementation
     * @return ImplAsymmKeyGenerator
     */
    protected abstract function getImpl() : ImplAsymmKeyGenerator;
}