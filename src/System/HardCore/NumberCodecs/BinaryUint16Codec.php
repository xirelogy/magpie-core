<?php

namespace Magpie\System\HardCore\NumberCodecs;

/**
 * Codec for representation of 16-bit unsigned integer numbers in binary
 */
class BinaryUint16Codec extends BinaryIntCodec
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
        return true;
    }


    /**
     * @inheritDoc
     */
    protected static function getPackFormatString(?Endian $endian) : string
    {
        return match ($endian) {
            Endian::LITTLE => 'v',
            Endian::BIG => 'n',
            default => 'S',
        };
    }
}