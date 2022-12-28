<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\General\Traits\StaticClass;

/**
 * Common symmetric cipher algorithms
 */
class CommonCipherAlgoTypeClass
{
    use StaticClass;


    /**
     * AES cipher (Advanced Encryption Standard)
     */
    public const AES = 'aes';
    /**
     * Blowfish cipher
     */
    public const BLOWFISH = 'blowfish';
    /**
     * CAST5 (CAST-128) cipher
     */
    public const CAST5 = 'cast5';
    /**
     * DES cipher (Data Encryption Standard), legacy
     */
    public const DES = 'des';
    /**
     * 2-key Triple-DES cipher, legacy
     */
    public const TRIPLE_DES_EDE = 'triple-des-ede';
    /**
     * 3-key Triple-DES cipher, legacy
     */
    public const TRIPLE_DES_EDE3 = 'triple-des-ede3';
}