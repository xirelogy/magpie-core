<?php

namespace Magpie\System\HardCore\NumberCodecs;

use Magpie\Exceptions\UnsupportedValueException;

/**
 * Codec for representation of 64-bit signed integer numbers in binary
 */
class BinaryInt64Codec extends BinaryIntCodec
{
    /**
     * @inheritDoc
     */
    public static function getBitSize() : int
    {
        return 64;
    }


    /**
     * @inheritDoc
     */
    public static function isUnsigned() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    protected static function getPackFormatString(?Endian $endian) : string
    {
        if ($endian === null) return 'q';

        throw new UnsupportedValueException($endian);
    }
}