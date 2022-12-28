<?php

namespace Magpie\System\HardCore\NumberCodecs;

use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataValueException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Traits\StaticClass;

/**
 * Codec for representation of integer numbers in binary
 */
abstract class BinaryIntCodec
{
    use StaticClass;


    /**
     * Size, in number of bits
     * @return int
     */
    public static abstract function getBitSize() : int;


    /**
     * If the target integer number is treated unsigned (otherwise signed)
     * @return bool
     */
    public static abstract function isUnsigned() : bool;


    /**
     * Encode a number into binary string
     * @param int $value
     * @param Endian|null $endian
     * @return string
     * @throws UnsupportedException
     */
    public static final function encode(int $value, ?Endian $endian = null) : string
    {
        $format = static::getPackFormatString($endian);
        return pack($format, $value);
    }


    /**
     * Encode a number into binary string (little-endian)
     * @param int $value
     * @return string
     * @throws UnsupportedException
     */
    public static final function encodeLittleEndian(int $value) : string
    {
        return static::encode($value, Endian::LITTLE);
    }


    /**
     * Encode a number into binary string (big-endian)
     * @param int $value
     * @return string
     * @throws UnsupportedException
     */
    public static final function encodeBigEndian(int $value) : string
    {
        return static::encode($value, Endian::BIG);
    }


    /**
     * Decode a number from binary string
     * @param string $value
     * @param Endian|null $endian
     * @return int
     * @throws UnsupportedException
     * @throws InvalidDataException
     */
    public static final function decode(string $value, ?Endian $endian = null) : int
    {
        $format = static::getPackFormatString($endian);
        $ret = unpack($format, $value);
        if ($ret === false) throw new InvalidDataValueException($value);

        return iter_first($ret) ?? throw new InvalidDataValueException($value);
    }


    /**
     * Decode a number from binary string (little-endian)
     * @param string $value
     * @return int
     * @throws InvalidDataException
     * @throws UnsupportedException
     */
    public static final function decodeLittleEndian(string $value) : int
    {
        return static::decode($value, Endian::LITTLE);
    }


    /**
     * Decode a number from binary string (big-endian)
     * @param string $value
     * @return int
     * @throws InvalidDataException
     * @throws UnsupportedException
     */
    public static final function decodeBigEndian(string $value) : int
    {
        return static::decode($value, Endian::BIG);
    }


    /**
     * Get corresponding format string for the pack()/unpack() function
     * @param Endian|null $endian
     * @return string
     * @throws UnsupportedException
     */
    protected static abstract function getPackFormatString(?Endian $endian) : string;
}