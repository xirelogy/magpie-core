<?php

namespace Magpie\Cryptos\Algorithms\Hashes;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\CommonNativeHasher;
use Magpie\Cryptos\Impls\CommonNativeHashType;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;

/**
 * Hasher provider
 */
abstract class Hasher implements TypeClassable
{
    /**
     * Calculate hash
     * @param string|BinaryDataProvidable|FileSystemAccessible $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public function hash(string|BinaryDataProvidable|FileSystemAccessible $data) : BinaryData
    {
        if ($data instanceof FileSystemAccessible) return $this->onHashFile($data->getFileSystemPath());

        $rawData = static::readRawData($data);
        return $this->onHash($rawData);
    }


    /**
     * Calculate hash (for data stored in string)
     * @param string $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function hashString(string $data) : BinaryData
    {
        return $this->onHash($data);
    }


    /**
     * Handle calculation of hash from file
     * @param string $path
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    protected function onHashFile(string $path) : BinaryData
    {
        $rawData = LocalRootFileSystem::instance()->readFile($path)->getData();
        return $this->onHash($rawData);
    }


    /**
     * Handle calculation of hash
     * @param string $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract function onHash(string $data) : BinaryData;


    /**
     * Read raw data
     * @param BinaryDataProvidable|string $data
     * @return string
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected static function readRawData(BinaryDataProvidable|string $data) : string
    {
        if (is_string($data)) return $data;

        return $data->getData();
    }


    /**
     * Hash with specific type class
     * @param string $typeClass
     * @param string|BinaryDataProvidable|FileSystemAccessible $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public static function hashWith(string $typeClass, string|BinaryDataProvidable|FileSystemAccessible $data) : BinaryData
    {
        return static::initialize($typeClass)->hash($data);
    }


    /**
     * Hash string data with specific type class
     * @param string $typeClass
     * @param string $data
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function hashStringWith(string $typeClass, string $data) : BinaryData
    {
        return static::initialize($typeClass)->hashString($data);
    }


    /**
     * Initialize a hasher instance
     * @param string $typeClass
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initialize(string $typeClass) : static
    {
        $nativeAlgo = CommonNativeHashType::checkTypeClass($typeClass);
        if ($nativeAlgo !== null) {
            return CommonNativeHasher::createNativeInstance($typeClass, $nativeAlgo);
        }

        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize();
    }


    /**
     * Initialize specific hasher instance
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specificInitialize() : static;
}