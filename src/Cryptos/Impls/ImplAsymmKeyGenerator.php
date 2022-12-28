<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for asymmetric key generator implementation
 * @internal
 */
interface ImplAsymmKeyGenerator
{
    /**
     * Generate the key
     * @return ImplAsymmKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function go() : ImplAsymmKey;
}