<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\AeadDecryptionCipherContext;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\AeadEncryptionCipherContext;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherContext;
use Magpie\Cryptos\Exceptions\DecryptionFailedException;
use Magpie\Cryptos\Exceptions\EncryptionFailedException;
use Magpie\Cryptos\Exceptions\WrongPaddingCryptoException;
use Magpie\Cryptos\Paddings\NoPadding;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Objects\BinaryData;

/**
 * OpenSSL implementation for symmetric cipher (encryption/decryption part)
 * @internal
 */
class SpecCipher extends Cipher
{
    /**
     * @var SpecImplSymmCipher Cipher implementation
     */
    protected readonly SpecImplSymmCipher $impl;
    /**
     * @var string Algorithm name (as in OpenSSL)
     */
    protected readonly string $openSslAlgoName;


    /**
     * Constructor
     * @param SpecImplSymmCipher $impl
     * @param string $openSslAlgoName
     * @param string $key
     * @param string|null $iv
     * @param string|null $mode
     * @param Padding|null $padding
     */
    public function __construct(SpecImplSymmCipher $impl, string $openSslAlgoName, string $key, ?string $iv, ?string $mode, ?Padding $padding)
    {
        $padding = $padding ?? new NoPadding();

        parent::__construct($key, $iv, $mode, $padding);

        $this->impl = $impl;
        $this->openSslAlgoName = $openSslAlgoName;
    }


    /**
     * @inheritDoc
     */
    protected function onEncryptBinary(string $plaintext, ?CipherContext $context) : string
    {
        $plaintext = $this->padding->encode($plaintext);

        if ($context instanceof AeadEncryptionCipherContext) {
            $outTag = '';
            $aad = $context->aad->asBinary();
            $tagLength = $context->tagLength;
            $ciphertext = ErrorHandling::execute(function () use($plaintext, &$outTag, $aad, $tagLength) {
                return openssl_encrypt($plaintext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? '', $outTag, $aad, $tagLength);
            });
            $context->outTag = BinaryData::fromBinary($outTag);
        } else {
            $ciphertext = ErrorHandling::execute(fn () => openssl_encrypt($plaintext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? ''));
        }

        if ($ciphertext === false) throw new EncryptionFailedException(previous: ErrorHandling::captureError());

        return $ciphertext;
    }


    /**
     * @inheritDoc
     */
    protected function onDecryptBinary(string $ciphertext, ?CipherContext $context) : string
    {
        if ($context instanceof AeadDecryptionCipherContext) {
            $tag = $context->tag->asBinary();
            $aad = $context->aad->asBinary();
            $plaintext = ErrorHandling::execute(fn () => openssl_decrypt($ciphertext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? '', $tag, $aad));
        } else {
            $plaintext = ErrorHandling::execute(fn () => openssl_decrypt($ciphertext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? ''));
        }

        if ($plaintext === false) throw ErrorHandling::captureError();

        try {
            return $this->padding->decode($plaintext);
        } catch (WrongPaddingCryptoException $ex) {
            throw new DecryptionFailedException(previous: $ex);
        }
    }
}