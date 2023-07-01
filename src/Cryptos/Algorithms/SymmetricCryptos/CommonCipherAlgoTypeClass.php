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
     * ARIA cipher
     */
    public const ARIA = 'aria';
    /**
     * Blowfish cipher
     */
    public const BLOWFISH = 'blowfish';
    /**
     * Camellia cipher
     */
    public const CAMELLIA = 'camellia';
    /**
     * CAST5 (CAST-128) cipher
     */
    public const CAST5 = 'cast5';
    /**
     * RC2, legacy
     */
    public const RC2 = 'rc2';
    /**
     * RC4, legacy
     */
    public const RC4 = 'rc4';
    /**
     * DES cipher (Data Encryption Standard), legacy
     */
    public const DES = 'des';
    /**
     * 2-key DES cipher in EDE mode, legacy
     */
    public const DES_EDE = 'des-ede';
    /**
     * 3-key Triple-DES cipher in EDE mode, legacy
     */
    public const DES_EDE3 = 'des-ede3';
    /**
     * 2-key Triple-DES cipher, legacy
     * @deprecated
     */
    public const TRIPLE_DES_EDE = 'triple-des-ede';
    /**
     * 3-key Triple-DES cipher, legacy
     * @deprecated
     */
    public const TRIPLE_DES_EDE3 = 'triple-des-ede3';
}