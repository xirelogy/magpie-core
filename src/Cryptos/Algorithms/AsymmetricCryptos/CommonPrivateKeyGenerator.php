<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Impls\ImplAsymmKeyGenerator;

/**
 * A private key generator (common implementation)
 */
abstract class CommonPrivateKeyGenerator extends PrivateKeyGenerator
{
    /**
     * @inheritDoc
     */
    public function go() : CommonPrivateKey
    {
        $implKey = $this->getImpl()->go();
        return CommonPrivateKey::_fromRaw($this->getTypeClass(), $implKey);
    }


    /**
     * Get implementation
     * @return ImplAsymmKeyGenerator
     */
    protected abstract function getImpl() : ImplAsymmKeyGenerator;
}