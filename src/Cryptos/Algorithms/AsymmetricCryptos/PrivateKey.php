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
    public abstract function decrypt(string $ciphertext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string;


    /**
     * Sign using this private key
     * @param string $plaintext
     * @param Hasher|string $hashAlgorithm
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function sign(string $plaintext, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : BinaryData;


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
    public abstract function getPublicKey() : PublicKey;


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
     */
    protected static function onConstructImplKey(ImplAsymmKey $implKey) : static
    {
        $algoTypeClass = $implKey->getAlgoTypeClass();
        return CommonPrivateKey::_fromRaw($algoTypeClass, $implKey);
    }
}