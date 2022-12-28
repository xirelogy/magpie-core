<?php

namespace Magpie\Facades;

use Magpie\General\Concepts\Randomable;
use Magpie\General\Randoms\MtRandom;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\Kernel;

/**
 * Random facade
 */
class Random
{
    use StaticClass;


    /**
     * Generate a random integer within the given numeric range
     * @param int $min
     * @param int|null $max
     * @return int
     */
    public static function integer(int $min = 0, ?int $max = null) : int
    {
        return static::getRandomProvider()->integer($min, $max);
    }


    /**
     * Generate a random string of given length, picking from given character set
     * @param int $length
     * @param string $charset
     * @return string
     */
    public static function string(int $length, string $charset) : string
    {
        return static::getRandomProvider()->string($length, $charset);
    }


    /**
     * Generate a random bytes string of given length
     * @param int $length
     * @return string
     */
    public static function bytes(int $length) : string
    {
        return static::getRandomProvider()->bytes($length);
    }


    /**
     * Get the default random provider
     * @return Randomable
     */
    protected static function getRandomProvider() : Randomable
    {
        if (Kernel::hasCurrent()) {
            $provider = Kernel::current()->getProvider(Randomable::class);
            if ($provider instanceof Randomable) return $provider;
        }

        return MtRandom::instance();
    }
}