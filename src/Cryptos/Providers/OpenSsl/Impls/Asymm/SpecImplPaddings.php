<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\NoPadding;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Pkcs1OaepPadding;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Pkcs1Padding;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Ssl23Padding;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;

/**
 * OpenSSL support on asymmetric cryptography paddings
 * @inheritDoc
 */
class SpecImplPaddings
{
    use StaticClass;


    /**
     * Translate padding value
     * @param Padding|null $padding
     * @return int
     * @throws UnsupportedValueException
     */
    public static function translatePadding(?Padding $padding) : int
    {
        if ($padding === null) return OPENSSL_NO_PADDING;

        return match ($padding->getTypeClass()) {
            NoPadding::TYPECLASS => OPENSSL_NO_PADDING,
            Pkcs1Padding::TYPECLASS => OPENSSL_PKCS1_PADDING,
            Pkcs1OaepPadding::TYPECLASS => OPENSSL_PKCS1_OAEP_PADDING,
            Ssl23Padding::TYPECLASS => OPENSSL_SSLV23_PADDING,
            default => throw new UnsupportedValueException($padding, _l('padding')),
        };
    }


    /**
     * Calculate the proper chunk size for given operation, number of bits, and padding
     * @param int $numBits
     * @param int $openSslPadding
     * @param bool $isEncrypt
     * @return int
     * @throws UnsupportedException
     */
    public static function calculateChunkSize(int $numBits, int $openSslPadding, bool $isEncrypt) : int
    {
        $ret = intval(floor($numBits / 8));
        if (!$isEncrypt) return $ret;

        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($openSslPadding) {
            case OPENSSL_NO_PADDING:
                return $ret;
            case OPENSSL_PKCS1_PADDING:
            case OPENSSL_SSLV23_PADDING:
                return $ret - 11;
            case OPENSSL_PKCS1_OAEP_PADDING:
                return $ret - (2 * 20) - 2; // number of bytes of SHA1 hash = 20
            default:
                throw new UnsupportedException();
        }

    }
}