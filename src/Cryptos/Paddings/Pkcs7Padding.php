<?php

namespace Magpie\Cryptos\Paddings;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherSetup;
use Magpie\Cryptos\Paddings\Traits\CommonPkcsBlockPadding;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-7 padding
 */
#[FactoryTypeClass(Pkcs7Padding::TYPECLASS, Padding::class)]
class Pkcs7Padding extends Padding
{
    use CommonPkcsBlockPadding;

    /**
     * Current type class
     */
    public const TYPECLASS = 'pkcs7';

    /**
     * @var int The specific block size
     */
    public int $blockSize;


    /**
     * Constructor
     * @param int $blockSize
     */
    public function __construct(int $blockSize)
    {
        $this->blockSize = $blockSize;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function encode(string $payload) : string
    {
        return $this->blockEncode($payload, $this->blockSize);
    }


    /**
     * @inheritDoc
     */
    public function decode(string $payload) : string
    {
        return $this->blockDecode($payload, $this->blockSize);
    }


    /**
     * Create PKCS#7 padding specifically to suite given cipher setup
     * @param CipherSetup $setup
     * @return static
     */
    public static function forCipherSetup(CipherSetup $setup) : static
    {
        $size = intval(floor($setup->getBlockNumBits() / 8));
        return new static($size);
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static(Pkcs5Padding::BLOCK_SIZE);
    }
}