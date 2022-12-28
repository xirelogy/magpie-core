<?php

namespace Magpie\Cryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-5 padding
 */
#[FactoryTypeClass(Pkcs5Padding::TYPECLASS, Padding::class)]
class Pkcs5Padding extends Padding
{
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
        $pad = static::BLOCK_SIZE - (strlen($payload) % static::BLOCK_SIZE);
        return $payload . str_repeat(chr($pad), $pad);
    }


    /**
     * @inheritDoc
     */
    public function decode(string $payload) : string
    {
        $pad = ord($payload[strlen($payload) - 1]);
        if ($pad < 1 || $pad > static::BLOCK_SIZE) return $payload;

        return substr($payload, 0, strlen($payload) - $pad);
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static();
    }
}