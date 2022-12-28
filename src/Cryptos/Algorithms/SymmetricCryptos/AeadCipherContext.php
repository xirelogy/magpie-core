<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\Objects\BinaryData;

/**
 * AEAD context (for GCM/CCM modes)
 */
abstract class AeadCipherContext extends CipherContext
{
    /**
     * @var BinaryData Additional authentication data
     */
    public readonly BinaryData $aad;


    /**
     * Constructor
     * @param BinaryData $aad
     */
    protected function __construct(BinaryData $aad)
    {
        $this->aad = $aad;
    }
}