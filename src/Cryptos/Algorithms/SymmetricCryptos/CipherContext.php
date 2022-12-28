<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\General\Concepts\TypeClassable;
use Magpie\Objects\BinaryData;

/**
 * Additional cipher related context
 */
abstract class CipherContext implements TypeClassable
{
    /**
     * Accept binary data
     * @param BinaryData|string $data
     * @return BinaryData
     */
    protected static function acceptBinaryData(BinaryData|string $data) : BinaryData
    {
        if ($data instanceof BinaryData) return $data;
        return BinaryData::fromBinary($data);
    }
}