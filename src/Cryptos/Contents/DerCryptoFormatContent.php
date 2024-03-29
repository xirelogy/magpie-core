<?php

namespace Magpie\Cryptos\Contents;

use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\Objects\BinaryData;

/**
 * DER format to store cryptographic related data
 */
class DerCryptoFormatContent extends CryptoFormatContent
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'der';


    /**
     * @inheritDoc
     */
    protected function onGetBinaryBlocks() : iterable
    {
        $data = $this->data->getData();

        yield new BinaryBlockContent(null, BinaryData::fromBinary($data));
    }


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