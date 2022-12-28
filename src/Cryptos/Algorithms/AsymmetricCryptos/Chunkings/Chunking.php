<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings;

use Magpie\Cryptos\Concepts\BinaryProcessable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;

/**
 * Chunking strategy in asymmetric cryptography to handle plaintext/ciphertext longer
 * than the cipher bit size.
 */
abstract class Chunking implements TypeClassable
{
    /**
     * Perform chunking for encryption
     * @param BinaryProcessable $crypto
     * @param string $plaintext
     * @param int $maxSize
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function encrypt(BinaryProcessable $crypto, string $plaintext, int $maxSize) : string;


    /**
     * Perform chunking for decryption
     * @param BinaryProcessable $crypto
     * @param string $ciphertext
     * @param int $maxSize
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function decrypt(BinaryProcessable $crypto, string $ciphertext, int $maxSize) : string;


    /**
     * Accept a chunking specification
     * @param self|string|null $spec
     * @return static|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function accept(self|string|null $spec) : ?static
    {
        if ($spec === null) return null;
        if ($spec instanceof self) return $spec;

        return static::initialize($spec);
    }


    /**
     * Initialize a new chunking strategy
     * @param string $typeClass
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initialize(string $typeClass) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specInitialize();
    }


    /**
     * Specific initialization
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specInitialize() : static;
}