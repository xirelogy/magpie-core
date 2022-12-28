<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;

/**
 * Context to support implementation details
 * @internal
 */
abstract class ImplContext implements TypeClassable
{
    /**
     * Generate random bytes of specific bit size, preferable using cryptographically strong methods
     * @param int $numBits
     * @return BinaryData
     */
    public abstract function generateRandom(int $numBits) : BinaryData;


    /**
     * Create symmetric key cipher
     * @param string $algoTypeClass
     * @param int|null $blockNumBits
     * @return ImplSymmCipher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function createSymmetricCipher(string $algoTypeClass, ?int $blockNumBits) : ImplSymmCipher;


    /**
     * Parse for asymmetric key
     * @param CryptoContent|BinaryDataProvidable|string $source
     * @param bool $isPrivate
     * @return ImplAsymmKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function parseAsymmetricKey(CryptoContent|BinaryDataProvidable|string $source, bool $isPrivate) : ImplAsymmKey;


    /**
     * Create asymmetric key generator
     * @param string $algoTypeClass
     * @return ImplAsymmKeyGenerator
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function createAsymmetricKeyGenerator(string $algoTypeClass) : ImplAsymmKeyGenerator;


    /**
     * Find Elliptic Curve's curve parameter implementation by given common name
     * @param string $name
     * @return ImplEcCurve|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function findEcCurveByName(string $name) : ?ImplEcCurve;


    /**
     * Initialize new implementation context
     * @param string $typeClass
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initialize(string $typeClass) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize();
    }


    /**
     * Specifically initialize new implementation context
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specificInitialize() : static;
}