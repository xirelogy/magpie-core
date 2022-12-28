<?php

namespace Magpie\System\HardCore\NumberCodecs;

/**
 * Codec for representation of 64-bit unsigned integer numbers in binary
 */
class BinaryUint64Codec extends BinaryIntCodec
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
        return true;
    }


    /**
     * @inheritDoc
     */
    protected static function getPackFormatString(?Endian $endian) : string
    {
        return match ($endian) {
            Endian::LITTLE => 'P',
            Endian::BIG => 'J',
            default => 'Q',
        };
    }
}