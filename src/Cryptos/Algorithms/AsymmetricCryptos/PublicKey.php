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
 * Public key in the asymmetric key pair
 */
abstract class PublicKey extends Key implements Importable
{
    /**
     * Encrypt using this public key
     * @param string $plaintext
     * @param Padding|string|null $padding
     * @param Chunking|string|null $chunking
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function encrypt(string $plaintext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string
    {
        $padding = Padding::accept($padding);
        $chunking = Chunking::accept($chunking) ?? new NoChunking();

        $crypto = $this->getImpl()->preparePublicKeyEncryption($padding, $chunking, $maxSize);
        return $chunking->encrypt($crypto, $plaintext, $maxSize);
    }


    /**
     * Verify plaintext signature using this public key
     * @param string $plaintext
     * @param BinaryData $signature
     * @param Hasher|string $hashAlgorithm
     * @return bool
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function verify(string $plaintext, BinaryData $signature, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : bool
    {
        $hashTypeClass = $hashAlgorithm instanceof Hasher ? $hashAlgorithm->getTypeClass() : $hashAlgorithm;

        return $this->getImpl()->publicVerify($plaintext, $signature, $hashTypeClass);
    }


    /**
     * @inheritDoc
     */
    protected function getExportName() : string
    {
        return 'PUBLIC KEY';
    }


    /**
     * @inheritDoc
     */
    public static function import(CryptoContent|BinaryDataProvidable|string $source, ?Context $context = null) : static
    {
        return static::onImport($source, false, $context);
    }


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
     */
    protected abstract static function onSpecificFromRaw(ImplAsymmKey $implKey) : static;
}