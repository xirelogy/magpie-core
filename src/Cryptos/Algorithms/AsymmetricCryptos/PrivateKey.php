<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Objects\BinaryData;

/**
 * Private key in the asymmetric key pair
 */
abstract class PrivateKey extends Key implements Importable
{
    /**
     * Decrypt using this private key
     * @param BinaryData|string $ciphertext
     * @param Padding|string|null $padding
     * @param Chunking|string|null $chunking
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function decrypt(BinaryData|string $ciphertext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : BinaryData;


    /**
     * Sign using this private key
     * @param BinaryData|string $plaintext
     * @param Hasher|string|null $hashAlgorithm
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function sign(BinaryData|string $plaintext, Hasher|string|null $hashAlgorithm = null) : BinaryData;


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
    protected static function isImportAsPrivate() : ?bool
    {
        return true;
    }


    /**
     * Create the private key generator
     * @param Context|null $context
     * @return PrivateKeyGenerator<static>
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract static function generate(?Context $context = null) : PrivateKeyGenerator;
}