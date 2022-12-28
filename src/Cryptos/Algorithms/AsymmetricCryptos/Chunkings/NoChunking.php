<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings;

use Magpie\Cryptos\Concepts\BinaryProcessable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * No chunking is performed
 */
#[FactoryTypeClass(NoChunking::TYPECLASS, Chunking::class)]
class NoChunking extends Chunking
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'none';


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
    public function encrypt(BinaryProcessable $crypto, string $plaintext, int $maxSize) : string
    {
        return $crypto->process($plaintext);
    }


    /**
     * @inheritDoc
     */
    public function decrypt(BinaryProcessable $crypto, string $ciphertext, int $maxSize) : string
    {
        return $crypto->process($ciphertext);
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static();
    }
}