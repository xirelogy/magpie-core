<?php

namespace Magpie\Cryptos\Contents;

use Magpie\Cryptos\Encodings\Pem;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\Objects\BinaryData;

/**
 * PEM format to store cryptographic related data
 */
class PemCryptoFormatContent extends CryptoFormatContent
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'pem';


    /**
     * @inheritDoc
     */
    protected function onGetBinaryBlocks() : iterable
    {
        $data = $this->data->getData();

        // When PEM header not expected, decode directly
        if (!Pem::hasContentType($data)) {
            yield new BinaryBlockContent(null, BinaryData::fromBase64($data));
            return;
        }

        // Otherwise, iterate through all blocks
        foreach (Pem::decode($data) as $pemBlock) {
            yield new BinaryBlockContent($pemBlock->type, BinaryData::fromBase64($pemBlock->data));
        }
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