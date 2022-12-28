<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * SSL v2.3 padding
 */
#[FactoryTypeClass(Ssl23Padding::TYPECLASS, Padding::class)]
class Ssl23Padding extends Padding
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'ssl23';


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