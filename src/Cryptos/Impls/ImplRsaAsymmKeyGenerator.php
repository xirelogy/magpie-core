<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for RSA asymmetric key generator implementation
 * @internal
 */
interface ImplRsaAsymmKeyGenerator extends ImplAsymmKeyGenerator
{
    /**
     * Set the number of bits
     * @param int $numBits
     * @return void
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function setNumBits(int $numBits) : void;


    /**
     * @inheritDoc
     */
    public function go() : ImplRsaAsymmKey;
}