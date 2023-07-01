<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May provide a service interface to setup symmetric cipher
 */
interface SymmetricCipherSetupServiceable extends CommonSymmetricCipherSetupServiceable
{
    /**
     * Create cipher
     * @param string $key
     * @param string|null $iv
     * @param Padding|null $padding
     * @return Cipher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function createCipher(string $key, ?string $iv, ?Padding $padding) : Cipher;
}