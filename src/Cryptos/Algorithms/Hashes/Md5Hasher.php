<?php

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\Cryptos\Impls\Traits\HashFromFile;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Objects\BinaryData;

/**
 * MD5 hasher
 */
#[FactoryTypeClass(Md5Hasher::TYPECLASS, Hasher::class)]
class Md5Hasher extends Hasher
{
    use HashFromFile;


    /**
     * Current type class
     */
    public const TYPECLASS = CommonHashTypeClass::MD5;


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
        return @md5_file($path, true);
    }


    /**
     * @inheritDoc
     */
    protected function onHash(string $data) : BinaryData
    {
        return BinaryData::fromBinary(md5($data, true));
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }
}