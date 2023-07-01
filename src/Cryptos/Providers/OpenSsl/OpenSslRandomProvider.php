<?php

namespace Magpie\Cryptos\Providers\OpenSsl;

use Magpie\General\Concepts\Randomable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Randoms\RandomProvider;

/**
 * OpenSSL's random provider
 */
#[FactoryTypeClass(OpenSslRandomProvider::TYPECLASS, Randomable::class)]
class OpenSslRandomProvider extends RandomProvider
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'openssl';


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
        $max = $max ?? PHP_INT_MAX;
        $localMax = $max - $min;
        if ($localMax <= 0) return 0;

        ++$localMax;

        $log = log($localMax, 2);
        $numBytes = ceil($log / 8) + 1;
        $numBits = ceil($log + 1);
        $mask = ceil(1 << $numBits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($numBytes)));
            $rnd &= $mask;
        } while ($rnd >= $localMax);

        return $min + $rnd;
    }


    /**
     * @inheritDoc
     */
    public function bytes(int $length) : string
    {
        return openssl_random_pseudo_bytes($length);
    }
}