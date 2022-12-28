<?php

namespace Magpie\Cryptos\Paddings;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * A specialization of padding where no padding is applied
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
    public function encode(string $payload) : string
    {
        return $payload;
    }


    /**
     * @inheritDoc
     */
    public function decode(string $payload) : string
    {
        return $payload;
    }


    /**
     * @inheritDoc
     */
    protected static function specInitialize() : static
    {
        return new static();
    }
}