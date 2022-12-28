<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * PKCS-1 OAEP padding
 */
#[FactoryTypeClass(Pkcs1OaepPadding::TYPECLASS, Padding::class)]
class Pkcs1OaepPadding extends Padding
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'pkcs1-oaep';


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