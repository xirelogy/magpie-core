<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * No padding
 */
#[FactoryTypeClass(NoPadding::TYPECLASS, Padding::class)]
class NoPadding extends Padding
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
    protected static function specInitialize() : static
    {
        return new static();
    }
}