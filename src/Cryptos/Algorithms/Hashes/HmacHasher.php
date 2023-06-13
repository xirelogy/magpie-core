<?php

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\CommonNativeHashType;
use Magpie\Cryptos\Impls\CommonNativeHmacHasher;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Traits\StaticClass;
use Magpie\Objects\BinaryData;

/**
 * Hasher provider with HMAC
 */
abstract class HmacHasher
{
    use StaticClass;


    /**
     * Hash with specific type class
     * @param string $typeClass
     * @param BinaryData|string $keySpec
     * @param string|BinaryDataProvidable|FileSystemAccessible $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public static function hashWith(string $typeClass, BinaryData|string $keySpec, string|BinaryDataProvidable|FileSystemAccessible $data) : BinaryData
    {
        return static::initialize($typeClass, $keySpec)->hash($data);
    }


    /**
     * Hash string data with specific type class
     * @param string $typeClass
     * @param BinaryData|string $keySpec
     * @param string $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function hashStringWith(string $typeClass, BinaryData|string $keySpec, string $data) : BinaryData
    {
        return static::initialize($typeClass, $keySpec)->hashString($data);
    }


    /**
     * Initialize a HMAC hasher instance
     * @param string $typeClass
     * @param BinaryData|string $keySpec
     * @return Hasher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initialize(string $typeClass, BinaryData|string $keySpec) : Hasher
    {
        $key = BinaryData::acceptBinary($keySpec);
        $nativeAlgo = CommonNativeHashType::checkHmacTypeClass($typeClass);
        if ($nativeAlgo !== null) {
            return CommonNativeHmacHasher::createNativeInstance($typeClass, $nativeAlgo, $key);
        }

        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificHmacInitialize($key);
    }


    /**
     * Initialize specific hasher instance
     * @param BinaryData $key
     * @return Hasher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specificHmacInitialize(BinaryData $key) : Hasher;
}