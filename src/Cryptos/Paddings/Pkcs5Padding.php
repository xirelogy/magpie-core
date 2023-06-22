<?php

namespace Magpie\Cryptos\Paddings;

use Magpie\Cryptos\Paddings\Traits\CommonPkcsBlockPadding;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-5 padding
 */
#[FactoryTypeClass(Pkcs5Padding::TYPECLASS, Padding::class)]
class Pkcs5Padding extends Padding
{
    use CommonPkcsBlockPadding;

    /**
     * Current type class
     */
    public const TYPECLASS = 'pkcs5';
    /**
     * Common block size for PKCS-5
     */
    public const BLOCK_SIZE = 8;


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
        return $this->blockEncode($payload, static::BLOCK_SIZE);
    }


    /**
     * @inheritDoc
     */
    public function decode(string $payload) : string
    {
        return $this->blockDecode($payload, static::BLOCK_SIZE);
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static();
    }
}