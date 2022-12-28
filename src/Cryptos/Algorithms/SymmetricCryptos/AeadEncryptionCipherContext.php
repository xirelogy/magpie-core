<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\Objects\BinaryData;

/**
 * AEAD context (for GCM/CCM modes) during encryption
 */
class AeadEncryptionCipherContext extends AeadCipherContext
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'aead-encrypt';

    /**
     * @var BinaryData|null Authentication tag (output)
     */
    public ?BinaryData $outTag = null;
    /**
     * @var int Tag length
     */
    public readonly int $tagLength;


    /**
     * Constructor
     * @param BinaryData $aad
     * @param int $tagLength
     */
    protected function __construct(BinaryData $aad, int $tagLength)
    {
        parent::__construct($aad);

        $this->tagLength = $tagLength;
    }


    /**
     * Create an instance
     * @param BinaryData|string $aad Additional authentication data
     * @param int $tagLength The length of the authentication tag
     * @return static
     */
    public static function create(BinaryData|string $aad, int $tagLength = 16) : static
    {
        return new static(static::acceptBinaryData($aad), $tagLength);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}