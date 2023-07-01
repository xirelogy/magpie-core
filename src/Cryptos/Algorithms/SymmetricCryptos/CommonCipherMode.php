<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\General\Traits\StaticClass;

/**
 * Commonly used cipher modes
 */
class CommonCipherMode
{
    use StaticClass;

    /**
     * CBC: Cipher block chaining
     */
    public const CBC = 'cbc';

    /**
     * ECB: Electronic code book
     */
    public const ECB = 'ecb';

    /**
     * CCM: counter with cipher block chaining message authentication code
     */
    public const CCM = 'ccm';

    /**
     * CFB: Cipher feedback
     */
    public const CFB = 'cfb';

    /**
     * CFB-1: 1-bit cipher feedback
     */
    public const CFB1 = 'cfb1';

    /**
     * CFB-8: 8-bit cipher feedback
     */
    public const CFB8 = 'cfb8';

    /**
     * CTR: Counter
     */
    public const CTR = 'ctr';

    /**
     * GCM: Galois/counter mode - expects AEAD context
     */
    public const GCM = 'gcm';

    /**
     * SIV: Synthetic initialization vector
     */
    public const SIV = 'siv';
}