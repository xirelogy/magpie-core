<?php

namespace Magpie\Cryptos\Paddings;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherSetup;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-7 padding
 */
#[FactoryTypeClass(Pkcs7Padding::TYPECLASS, Padding::class)]
class Pkcs7Padding extends Padding
{
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
        $blockSize = $this->blockSize;

        $pad = $blockSize - (strlen($payload) % $blockSize);
        if ($pad === 0) $pad = $blockSize;
        return $payload . str_repeat(chr($pad), $pad);
    }


    /**
     * @inheritDoc
     */
    public function decode(string $payload) : string
    {
        $blockSize = $this->blockSize;

        $pad = ord($payload[strlen($payload) - 1]);
        if ($pad < 1 || $pad > $blockSize) return $payload;

        return substr($payload, 0, strlen($payload) - $pad);
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