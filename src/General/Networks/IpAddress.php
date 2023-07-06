<?php

namespace Magpie\General\Networks;

use Exception;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Sugars\Excepts;
use Magpie\Objects\BinaryData;

/**
 * Representation of an IP address
 */
abstract class IpAddress implements PreferStringable, TypeClassable
{
    /**
     * Obtain the corresponding binary representation of the address
     * @return BinaryData
     * @throws SafetyCommonException
     */
    public abstract function getBinary() : BinaryData;


    /**
     * @inheritDoc
     */
    public final function __toString() : string
    {
        return Excepts::noThrow(fn () => $this->onFormatAsString(), '?');
    }


    /**
     * Format as string
     * @return string
     * @throws Exception
     */
    protected abstract function onFormatAsString() : string;


    /**
     * Number of bits in this kind of address
     * @return int
     */
    public static abstract function getNumBits() : int;


    /**
     * Parse for an address from given string representation
     * @param string $value
     * @return static
     * @throws SafetyCommonException
     */
    public static final function parse(string $value) : static
    {
        return static::onParse($value);
    }


    /**
     * Parse for an address from given string representation
     * @param string $value
     * @return static
     * @throws SafetyCommonException
     */
    protected static function onParse(string $value) : static
    {
        if (str_contains($value, ':')) {
            return Ipv6Address::onParse($value);
        }

        if (str_contains($value, '.')) {
            return Ipv4Address::onParse($value);
        }

        throw new InvalidDataException();
    }


    /**
     * Construct address from given binary representation
     * @param BinaryData|string $value
     * @return static
     * @throws SafetyCommonException
     */
    public static final function fromBinary(BinaryData|string $value) : static
    {
        $value = BinaryData::acceptBinary($value)->asBinary();
        return static::onFromBinary($value);
    }


    /**
     * Construct address from given binary representation
     * @param string $binString
     * @return static
     * @throws SafetyCommonException
     */
    protected static function onFromBinary(string $binString) : static
    {
        $valueLength = strlen($binString);

        if ($valueLength === 16) return Ipv6Address::onFromBinary($binString);
        if ($valueLength === 4) return Ipv4Address::onFromBinary($binString);

        throw new InvalidDataException();
    }
}