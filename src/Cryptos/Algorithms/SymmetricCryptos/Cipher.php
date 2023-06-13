<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Objects\BinaryData;

/**
 * A symmetric crypto cipher
 */
abstract class Cipher implements Packable
{
    use CommonPackable;


    /**
     * @var string Cipher key
     */
    protected readonly string $key;
    /**
     * @var string|null Cipher IV
     */
    protected readonly ?string $iv;
    /**
     * @var string|null Cipher mode
     */
    protected readonly ?string $mode;
    /**
     * @var Padding|null Selected padding
     */
    protected readonly ?Padding $padding;


    /**
     * Constructor
     * @param string $key
     * @param string|null $iv
     * @param string|null $mode
     * @param Padding|null $padding
     */
    protected function __construct(string $key, ?string $iv, ?string $mode, ?Padding $padding)
    {
        $this->key = $key;
        $this->iv = $iv;
        $this->mode = $mode;
        $this->padding = $padding;
    }


    /**
     * Encrypt data
     * @param BinaryData|string $plaintext
     * @param CipherContext|null $context
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public final function encrypt(BinaryData|string $plaintext, ?CipherContext $context = null) : BinaryData
    {
        $plaintext = BinaryData::acceptBinary($plaintext)->asBinary();
        $ciphertext = $this->onEncryptBinary($plaintext, $context);

        return BinaryData::fromBinary($ciphertext);
    }


    /**
     * Handle encrypting data
     * @param string $plaintext
     * @param CipherContext|null $context
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract function onEncryptBinary(string $plaintext, ?CipherContext $context) : string;


    /**
     * Decrypt data
     * @param BinaryData|string $ciphertext
     * @param CipherContext|null $context
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public final function decrypt(BinaryData|string $ciphertext, ?CipherContext $context = null) : BinaryData
    {
        $ciphertext = BinaryData::acceptBinary($ciphertext)->asBinary();
        $plaintext = $this->onDecryptBinary($ciphertext, $context);

        return BinaryData::fromBinary($plaintext);
    }


    /**
     * Handle decrypting data
     * @param string $ciphertext
     * @param CipherContext|null $context
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract function onDecryptBinary(string $ciphertext, ?CipherContext $context) : string;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        // purposely NOP as default
    }
}