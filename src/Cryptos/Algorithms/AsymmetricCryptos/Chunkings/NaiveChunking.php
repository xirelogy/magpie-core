<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings;

use Magpie\Cryptos\Concepts\BinaryProcessable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * Naive chunking is simply concatenating the chunks
 */
#[FactoryTypeClass(NaiveChunking::TYPECLASS, Chunking::class)]
class NaiveChunking extends Chunking
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'naive';


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
        $ciphertext = '';

        foreach (str_split($plaintext, $maxSize) as $chunk) {
            $ciphertext .= $crypto->process($chunk);
        }

        return $ciphertext;
    }


    /**
     * @inheritDoc
     */
    public function decrypt(BinaryProcessable $crypto, string $ciphertext, int $maxSize) : string
    {
        $plaintext = '';

        foreach (str_split($ciphertext, $maxSize) as $chunk) {
            $plaintext .= $crypto->process($chunk);
        }

        return $plaintext;
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static();
    }
}