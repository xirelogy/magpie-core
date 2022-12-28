<?php

namespace Magpie\System\HardCore\NumberCodecs;

/**
 * Codec for representation of 32-bit unsigned integer numbers in binary
 */
class BinaryUint32Codec extends BinaryIntCodec
{
    /**
     * @inheritDoc
     */
    public static function getBitSize() : int
    {
        return 32;
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
            Endian::LITTLE => 'V',
            Endian::BIG => 'N',
            default => 'L',
        };
    }
}