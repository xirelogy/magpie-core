<?php

namespace Magpie\General\Randoms;

use Magpie\General\Concepts\Randomable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Traits\CommonRandomable;
use Magpie\General\Traits\SingletonInstance;

/**
 * Random number generator using Mersenne Twister characteristics
 */
#[FactoryTypeClass(MtRandom::TYPECLASS, Randomable::class)]
class MtRandom implements Randomable
{
    use SingletonInstance;
    use CommonRandomable;

    /**
     * Current type class
     */
    public const TYPECLASS = 'mt';


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
    public function integer(int $min = 0, ?int $max = null) : int
    {
        return mt_rand($min, $max ?? mt_getrandmax());
    }
}