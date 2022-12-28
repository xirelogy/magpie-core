<?php

/** @noinspection PhpUnused */

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\General\Traits\StaticClass;

/**
 * Common type class for hash algorithms
 */
class CommonHashTypeClass
{
    use StaticClass;

    // Very common algorithms
    public const MD5 = 'md5';
    public const SHA1 = 'sha1';

    // Legacy MD
    public const MD2 = 'md2';
    public const MD4 = 'md4';

    // SHA-2 families
    public const SHA224 = 'sha224';
    public const SHA256 = 'sha256';
    public const SHA384 = 'sha384';
    public const SHA512 = 'sha512';
    public const SHA512_224 = 'sha512-224';     // sha512/224
    public const SHA512_256 = 'sha512-256';     // sha512/256

    // SHA-3 families
    public const SHA3_224 = 'sha3-224';
    public const SHA3_256 = 'sha3-256';
    public const SHA3_384 = 'sha3-384';
    public const SHA3_512 = 'sha3-512';

    // RIPE-MD
    public const RIPEMD128 = 'ripemd128';
    public const RIPEMD160 = 'ripemd160';
    public const RIPEMD256 = 'ripemd256';
    public const RIPEMD320 = 'ripemd320';

    // CRCs, non-crypto
    public const CRC32 = 'crc32';
    public const CRC32B = 'crc32b';
    public const CRC32C = 'crc32c';

    // FNV (Fowler–Noll–Vo), non-crypto
    public const FNV1_32 = 'fnv1-32';
    public const FNV1A_32 = 'fnv1a-32';
    public const FNV1_64 = 'fnv1-64';
    public const FNV1A_64 = 'fnv1a-64';
}