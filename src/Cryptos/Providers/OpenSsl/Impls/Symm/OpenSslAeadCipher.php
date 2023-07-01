<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\AeadDecryptionCipherContext;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\AeadEncryptionCipherContext;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherContext;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Objects\BinaryData;

/**
 * Symmetric cipher that expects AEAD context using OpenSSL as provider
 * @internal
 */
class OpenSslAeadCipher extends OpenSslCipher
{
    /**
     * @inheritDoc
     */
    protected function onOpenSslEncryptBinary(string $plaintext, ?CipherContext $context) : string|false
    {
        if (!$context instanceof AeadEncryptionCipherContext) throw new NotOfTypeException($context, AeadEncryptionCipherContext::class);

        $outTag = '';
        $aad = $context->aad->asBinary();
        $tagLength = $context->tagLength;
        $ciphertext = ErrorHandling::execute(function () use($plaintext, &$outTag, $aad, $tagLength) {
            return openssl_encrypt($plaintext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? '', $outTag, $aad, $tagLength);
        });
        $context->outTag = BinaryData::fromBinary($outTag);

        return $ciphertext;
    }


    /**
     * @inheritDoc
     */
    protected function onOpenSslDecryptBinary(string $ciphertext, ?CipherContext $context) : string|false
    {
        if (!$context instanceof AeadDecryptionCipherContext) throw new NotOfTypeException($context, AeadDecryptionCipherContext::class);

        $tag = $context->tag->asBinary();
        $aad = $context->aad->asBinary();

        return ErrorHandling::execute(fn () => openssl_decrypt($ciphertext, $this->openSslAlgoName, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv ?? '', $tag, $aad));
    }
}