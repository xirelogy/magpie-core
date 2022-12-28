<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-1 padding
 */
#[FactoryTypeClass(Pkcs1Padding::TYPECLASS, Padding::class)]
class Pkcs1Padding extends Padding
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'pkcs1';


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
    protected static function specInitialize() : static
    {
        return new static();
    }
}