<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Objects\BinaryData;

/**
 * Public key in the asymmetric key pair
 */
abstract class PublicKey extends Key implements Importable
{
    /**
     * Encrypt using this public key
     * @param BinaryData|string $plaintext
     * @param Padding|string|null $padding
     * @param Chunking|string|null $chunking
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function encrypt(BinaryData|string $plaintext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string;


    /**
     * Verify plaintext signature using this public key
     * @param BinaryData|string $plaintext
     * @param BinaryData $signature
     * @param Hasher|string $hashAlgorithm
     * @return bool
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function verify(BinaryData|string $plaintext, BinaryData $signature, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : bool;


    /**
     * @inheritDoc
     */
    protected static function isImportAsPrivate() : ?bool
    {
        return false;
    }
}