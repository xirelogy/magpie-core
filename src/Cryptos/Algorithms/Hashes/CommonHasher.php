<?php

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Traits\StaticClass;

/**
 * Common hasher provider instances
 */
class CommonHasher
{
    use StaticClass;


    /**
     * MD5 hasher instance
     * @return Hasher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function md5() : Hasher
    {
        return Hasher::initialize(CommonHashTypeClass::MD5);
    }


    /**
     * SHA1 hasher instance
     * @return Hasher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function sha1() : Hasher
    {
        return Hasher::initialize(CommonHashTypeClass::SHA1);
    }
}