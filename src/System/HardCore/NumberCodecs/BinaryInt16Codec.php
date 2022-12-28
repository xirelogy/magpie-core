<?php

namespace Magpie\System\HardCore\NumberCodecs;

use Magpie\Exceptions\UnsupportedValueException;

/**
 * Codec for representation of 16-bit signed integer numbers in binary
 */
class BinaryInt16Codec extends BinaryIntCodec
{
    /**
     * @inheritDoc
     */
    public static function getBitSize() : int
    {
        return 16;
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
        if ($endian === null) return 's';

        throw new UnsupportedValueException($endian);
    }
}