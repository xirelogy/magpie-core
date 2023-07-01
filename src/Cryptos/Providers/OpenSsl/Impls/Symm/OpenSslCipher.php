<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherContext;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\DecryptionFailedException;
use Magpie\Cryptos\Exceptions\EncryptionFailedException;
use Magpie\Cryptos\Exceptions\WrongPaddingCryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Symmetric cipher using OpenSSL as provider
 * @internal
 */
class OpenSslCipher extends Cipher
{
    /**
     * @var string Algorithm name (as in OpenSSL)
     */
    protected readonly string $openSslAlgoName;


    /**
     * Constructor
     * @param string $openSslAlgoName
     * @param string $key
     * @param string|null $iv
     * @param string|null $mode
     * @param Padding|null $padding
     */
    public function __construct(string $openSslAlgoName, string $key, ?string $iv, ?string $mode, ?Padding $padding)
    {
        parent::__construct($key, $iv, $mode, $padding);

        $this->openSslAlgoName = $openSslAlgoName;
    }


    /**
     * @inheritDoc
     */
    protected final function onEncryptBinary(string $plaintext, ?CipherContext $context) : string
    {
        // Plaintext shall be properly padded before encryption
        $plaintext = $this->padding?->encode($plaintext) ?? $plaintext;

        $ciphertext = $this->onOpenSslEncryptBinary($plaintext, $context);
        if ($ciphertext === false) throw new EncryptionFailedException(previous: ErrorHandling::captureError());

        return $ciphertext;
    }


    /**
     * Handle encrypting data using OpenSSL
     * @param string $plaintext
     * @param CipherContext|null $context
     * @return string|false
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function onOpenSslEncryptBinary(string $plaintext, ?CipherContext $context) : string|false
    {
        _used($context);
        _throwable() ?? throw new NullException();

        return ErrorHandling::execute(fn () => openssl_encrypt($plaintext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? ''));
    }


    /**
     * @inheritDoc
     */
    protected final function onDecryptBinary(string $ciphertext, ?CipherContext $context) : string
    {
        $plaintext = $this->onOpenSslDecryptBinary($ciphertext, $context);
        if ($plaintext === false) throw ErrorHandling::captureError();

        if ($this->padding === null) return $plaintext;

        // Decode from padding, and when failed, this probably indicates a decryption failure
        try {
            return $this->padding->decode($plaintext);
        } catch (WrongPaddingCryptoException $ex) {
            throw new DecryptionFailedException(previous: $ex);
        }
    }


    /**
     * Handle decrypting data using OpenSSL
     * @param string $ciphertext
     * @param CipherContext|null $context
     * @return string|false
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function onOpenSslDecryptBinary(string $ciphertext, ?CipherContext $context) : string|false
    {
        _used($context);
        _throwable() ?? throw new NullException();

        return ErrorHandling::execute(fn () => openssl_decrypt($ciphertext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? ''));
    }
}