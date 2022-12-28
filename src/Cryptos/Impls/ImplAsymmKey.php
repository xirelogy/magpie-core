<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Concepts\BinaryProcessable;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Objects\BinaryData;

/**
 * Interface for asymmetric key implementation
 * @internal
 */
interface ImplAsymmKey extends AlgoTypeClassable
{
    /**
     * Number of bits in the current key
     * @return int
     */
    public function getNumBits() : int;


    /**
     * Get public key equivalent
     * @return ImplAsymmKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function getPublic() : ImplAsymmKey;


    /**
     * Prepare a public key encryption interface
     * @param Padding|null $padding
     * @param Chunking|null $chunking
     * @param int|null $maxSize
     * @return BinaryProcessable
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function preparePublicKeyEncryption(?Padding $padding, ?Chunking $chunking, ?int &$maxSize = null) : BinaryProcessable;


    /**
     * Prepare a private key decryption interface
     * @param Padding|null $padding
     * @param Chunking|null $chunking
     * @param int|null $maxSize
     * @return BinaryProcessable
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function preparePrivateKeyDecryption(?Padding $padding, ?Chunking $chunking, ?int &$maxSize = null) : BinaryProcessable;


    /**
     * Sign using private key
     * @param string $plaintext
     * @param string $hashTypeClass
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function privateSign(string $plaintext, string $hashTypeClass) : BinaryData;


    /**
     * Verify using public key
     * @param string $plaintext
     * @param BinaryData $signature
     * @param string $hashTypeClass
     * @return bool
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function publicVerify(string $plaintext, BinaryData $signature, string $hashTypeClass) : bool;


    /**
     * Export the key
     * @param string $exportName
     * @param array<ExportOption> $options
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function export(string $exportName, array $options) : string;
}