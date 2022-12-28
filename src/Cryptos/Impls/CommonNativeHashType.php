<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\General\Traits\StaticClass;

/**
 * Mappings for common native hash types
 * @internal
 */
class CommonNativeHashType
{
    use StaticClass;


    /**
     * Accept and check native hash type into those supported by the native hash function
     * @param string $typeClass
     * @return string|null
     */
    public static function checkTypeClass(string $typeClass) : ?string
    {
        $accepted = static::acceptTypeClass($typeClass);
        if ($accepted === null) return null;

        // Check that the accepted native hash type is in supported list
        if (!in_array($accepted, hash_algos())) return null;

        return $accepted;
    }


    /**
     * Accept and check native hash type into those supported by the native hash_hmac function
     * @param string $typeClass
     * @return string|null
     */
    public static function checkHmacTypeClass(string $typeClass) : ?string
    {
        $accepted = static::acceptTypeClass($typeClass);
        if ($accepted === null) return null;

        // Check that the accepted native hash type is in supported list
        if (!in_array($accepted, hash_hmac_algos())) return null;

        return $accepted;
    }


    /**
     * Try to accept native hash type into those supported by the native hash function
     * @param string $typeClass
     * @return string|null
     */
    public static function acceptTypeClass(string $typeClass) : ?string
    {
        return match ($typeClass) {
            CommonHashTypeClass::MD2 => 'md2',
            CommonHashTypeClass::MD4 => 'md4',
            CommonHashTypeClass::MD5 => 'md5',
            CommonHashTypeClass::SHA1 => 'sha1',
            CommonHashTypeClass::SHA224 => 'sha224',
            CommonHashTypeClass::SHA256 => 'sha256',
            CommonHashTypeClass::SHA384 => 'sha384',
            CommonHashTypeClass::SHA512 => 'sha512',
            CommonHashTypeClass::SHA512_224 => 'sha512/224',
            CommonHashTypeClass::SHA512_256 => 'sha512/256',
            CommonHashTypeClass::SHA3_224 => 'sha3-224',
            CommonHashTypeClass::SHA3_256 => 'sha3-256',
            CommonHashTypeClass::SHA3_384 => 'sha3-384',
            CommonHashTypeClass::SHA3_512 => 'sha3-512',
            CommonHashTypeClass::RIPEMD128 => 'ripemd128',
            CommonHashTypeClass::RIPEMD160 => 'ripemd160',
            CommonHashTypeClass::RIPEMD256 => 'ripemd256',
            CommonHashTypeClass::RIPEMD320 => 'ripemd320',
            CommonHashTypeClass::CRC32 => 'crc32',
            CommonHashTypeClass::CRC32B => 'crc32b',
            CommonHashTypeClass::CRC32C => 'crc32c',
            CommonHashTypeClass::FNV1_32 => 'fnv132',
            CommonHashTypeClass::FNV1A_32 => 'fnv1a32',
            CommonHashTypeClass::FNV1_64 => 'fnv164',
            CommonHashTypeClass::FNV1A_64 => 'fnv1a64',
            default => null,
        };
    }
}