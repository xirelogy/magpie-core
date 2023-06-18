<?php

namespace Magpie\General\Randoms;

use Exception;
use Magpie\General\Concepts\Randomable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Cryptographic-safe random number generator
 */
#[FactoryTypeClass(CryptographicSafeRandomProvider::TYPECLASS, Randomable::class)]
class CryptographicSafeRandomProvider extends RandomProvider
{
    use SingletonInstance;

    /**
     * Current type class
     */
    public const TYPECLASS = 'crypto-safe';


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
        try {
            return random_int($min, $max ?? PHP_INT_MAX);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function bytes(int $length) : string
    {
        try {
            return random_bytes($length);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }
}