<?php

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\Cryptos\Impls\Traits\HashFromFile;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Objects\BinaryData;

/**
 * SHA-1 hasher
 */
#[FactoryTypeClass(Sha1Hasher::TYPECLASS, Hasher::class)]
class Sha1Hasher extends Hasher
{
    use HashFromFile;


    /**
     * Current type class
     */
    public const TYPECLASS = CommonHashTypeClass::SHA1;


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
    protected function onHashFileNative(string $path) : string|false
    {
        return @sha1_file($path, true);
    }


    /**
     * @inheritDoc
     */
    protected function onHash(string $data) : BinaryData
    {
        return BinaryData::fromBinary(sha1($data, true));
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }
}