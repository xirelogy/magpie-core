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
    public abstract function encrypt(string $plaintext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string;


    /**
     * Verify plaintext signature using this public key
     * @param string $plaintext
     * @param BinaryData $signature
     * @param Hasher|string $hashAlgorithm
     * @return bool
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function verify(string $plaintext, BinaryData $signature, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : bool;


    /**
     * @inheritDoc
     */
    public static function import(CryptoContent|BinaryDataProvidable|string $source, ?Context $context = null) : static
    {
        return static::onImport($source, false, $context);
    }


    /**
     * @inheritDoc
     */
    protected static function onConstructImplKey(ImplAsymmKey $implKey) : static
    {
        $algoTypeClass = $implKey->getAlgoTypeClass();
        return CommonPublicKey::_fromRaw($algoTypeClass, $implKey);
    }
}