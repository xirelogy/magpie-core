<?php

namespace Magpie\Cryptos\Contents;

use Magpie\General\Concepts\BinaryDataProvidable;

/**
 * PKCS#12 format to store cryptographic related data
 */
class Pkcs12CryptoFormatContent extends CryptoFormatContent
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'pkcs12';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Create from data
     * @param BinaryDataProvidable $data
     * @param string|null $password
     * @return static
     */
    public static function fromData(BinaryDataProvidable $data, ?string $password = null) : static
    {
        return new static($data, $password);
    }
}