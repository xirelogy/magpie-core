<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\NoChunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;

/**
 * Private key in the asymmetric key pair
 */
abstract class PrivateKey extends Key implements Importable
{
    /**
     * Decrypt using this private key
     * @param string $ciphertext
     * @param Padding|string|null $padding
     * @param Chunking|string|null $chunking
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function decrypt(string $ciphertext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string
    {
        $padding = Padding::accept($padding);
        $chunking = Chunking::accept($chunking) ?? new NoChunking();

        $crypto = $this->getImpl()->preparePrivateKeyDecryption($padding, $chunking, $maxSize);
        return $chunking->decrypt($crypto, $ciphertext, $maxSize);
    }


    /**
     * Sign using this private key
     * @param string $plaintext
     * @param Hasher|string $hashAlgorithm
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function sign(string $plaintext, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : BinaryData
    {
        $hashTypeClass = $hashAlgorithm instanceof Hasher ? $hashAlgorithm->getTypeClass() : $hashAlgorithm;

        return $this->getImpl()->privateSign($plaintext, $hashTypeClass);
    }


    /**
     * If current private key is paired with public key
     * @param PublicKey $publicKey
     * @return bool
     */
    public abstract function isPairedWith(PublicKey $publicKey) : bool;


    /**
     * Get corresponding public key
     * @return PublicKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function getPublicKey() : PublicKey
    {
        $implKey = $this->getImpl()->getPublic();

        return PublicKey::_fromRaw($this->getAlgoTypeClass(), $implKey);
    }


    /**
     * @inheritDoc
     */
    protected function getExportName() : string
    {
        return 'PRIVATE KEY';
    }


    /**
     * @inheritDoc
     */
    public static function import(CryptoContent|BinaryDataProvidable|string $source, ?Context $context = null) : static
    {
        return static::onImport($source, true, $context);
    }


    /**
     * Create the private key generator
     * @param Context|null $context
     * @return PrivateKeyGenerator
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract static function generate(?Context $context = null) : PrivateKeyGenerator;


    /**
     * @inheritDoc
     * @internal
     */
    public static function _fromRaw(string $algoTypeClass, ImplAsymmKey $implKey) : static
    {
        $className = ClassFactory::resolve($algoTypeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::onSpecificFromRaw($implKey);
    }


    /**
     * Create specific key instance
     * @param ImplAsymmKey $implKey
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function onSpecificFromRaw(ImplAsymmKey $implKey) : static;
}