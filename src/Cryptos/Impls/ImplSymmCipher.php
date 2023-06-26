<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Concepts\CommonSymmetricCipherSetupServiceable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for symmetric cipher implementation
 * @internal
 */
interface ImplSymmCipher extends CommonSymmetricCipherSetupServiceable
{
    /**
     * Create cipher
     * @param string $key
     * @param string|null $iv
     * @param string|null $mode
     * @param Padding|null $padding
     * @return Cipher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function createCipher(string $key, ?string $iv, ?string $mode, ?Padding $padding) : Cipher;
}