<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\Objects\BinaryData;

/**
 * AEAD context (for GCM/CCM modes) during decryption
 */
class AeadDecryptionCipherContext extends AeadCipherContext
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'aead-decrypt';

    /**
     * @var BinaryData Authentication tag
     */
    public readonly BinaryData $tag;


    /**
     * Constructor
     * @param BinaryData $tag
     * @param BinaryData $aad
     */
    protected function __construct(BinaryData $tag, BinaryData $aad)
    {
        parent::__construct($aad);

        $this->tag = $tag;
    }


    /**
     * Create an instance
     * @param BinaryData|string $tag Authentication tag
     * @param BinaryData|string $aad Additional authentication data
     * @return static
     */
    public static function create(BinaryData|string $tag, BinaryData|string $aad) : static
    {
        return new static(
            BinaryData::acceptBinary($tag),
            BinaryData::acceptBinary($aad),
        );
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}