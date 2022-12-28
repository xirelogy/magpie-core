<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;

/**
 * Asymmetric signature algorithms
 * @internal
 */
class SpecImplAsymmSignature
{
    use StaticClass;


    /**
     * Translate signature algorithm
     * @param string $typeClass
     * @return int
     * @throws UnsupportedException
     */
    public static function translateHash(string $typeClass) : int
    {
        return match ($typeClass) {
            CommonHashTypeClass::SHA1 => OPENSSL_ALGO_SHA1,
            CommonHashTypeClass::SHA224 => OPENSSL_ALGO_SHA224,
            CommonHashTypeClass::SHA256 => OPENSSL_ALGO_SHA256,
            CommonHashTypeClass::SHA384 => OPENSSL_ALGO_SHA384,
            CommonHashTypeClass::SHA512 => OPENSSL_ALGO_SHA512,
            CommonHashTypeClass::MD5 => OPENSSL_ALGO_MD5,
            CommonHashTypeClass::MD4 => OPENSSL_ALGO_MD4,
            CommonHashTypeClass::RIPEMD160 => OPENSSL_ALGO_RMD160,
            default => throw new UnsupportedValueException($typeClass, _l('signature hash algorithm')),
        };
    }
}