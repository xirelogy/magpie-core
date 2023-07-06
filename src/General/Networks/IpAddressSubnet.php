<?php

namespace Magpie\General\Networks;

use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\OutOfRangeException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * IP address subnet
 */
final class IpAddressSubnet implements PreferStringable
{
    /**
     * @var IpAddress The address part
     */
    public readonly IpAddress $address;
    /**
     * @var int Subnet prefix
     */
    public readonly int $prefix;


    /**
     * Constructor
     * @param IpAddress $address
     * @param int $prefix
     */
    protected function __construct(IpAddress $address, int $prefix)
    {
        $this->address = $address;
        $this->prefix = $prefix;
    }


    /**
     * Corresponding net mask for current prefix
     * @return IpAddress
     * @throws SafetyCommonException
     */
    public function getNetMask() : IpAddress
    {
        $totalNumBits = $this->address::getNumBits();
        $bytes = '';
        $tempByte = 0;
        for ($i = 0; $i < $totalNumBits; ++$i) {
            $tempByte <<= 1;
            if ($i < $this->prefix) {
                $tempByte |= 1;
            }

            if (($i + 1) % 8 === 0) {
                $bytes .= chr($tempByte);
                $tempByte = 0;
            }
        }

        return IpAddress::fromBinary($bytes);
    }


    /**
     * Check if given address is in current subnet range
     * @param IpAddress $address
     * @return bool
     * @throws SafetyCommonException
     */
    public function isAddressInRange(IpAddress $address) : bool
    {
        if ($this->address::getTypeClass() !== $address::getTypeClass()) throw new InvalidDataException();
        $totalNumBits = $this->address::getNumBits();

        $thisNetwork = static::getAddressBinaryAtPrefix($this->address, $this->prefix, $totalNumBits);
        $candidateNetwork = static::getAddressBinaryAtPrefix($address, $this->prefix, $totalNumBits);

        return $thisNetwork == $candidateNetwork;
    }


    /**
     * Calculate the address network's binary string considering the prefix
     * @param IpAddress $address
     * @param int $prefix
     * @param int $totalNumBits
     * @return string
     * @throws SafetyCommonException
     */
    private static function getAddressBinaryAtPrefix(IpAddress $address, int $prefix, int $totalNumBits) : string
    {
        $binary = $address->getBinary()->asBinary();

        $bytes = '';
        $tempByte = 0;
        for ($i = 0; $i < $totalNumBits; ++$i) {
            $tempByte <<= 1;
            if ($i < $prefix) {
                $tempByte |= 1;
            }

            if (($i + 1) % 8 === 0) {
                $masked = ord(substr($binary, floor($i / 8), 1)) & $tempByte;
                $bytes .= chr($masked);
                $tempByte = 0;
            }
        }

        return $bytes;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->address . '/' . $this->prefix;
    }


    /**
     * Create an instance
     * @param IpAddress|string $address
     * @param int $prefix
     * @return static
     * @throws SafetyCommonException
     */
    public static function create(IpAddress|string $address, int $prefix) : static
    {
        $address = $address instanceof IpAddress ? $address : IpAddress::parse($address);
        if ($prefix < 0 || $prefix > $address::getNumBits()) throw new OutOfRangeException(_l('prefix out of range'));

        return new static($address, $prefix);
    }


    /**
     * Parse for an address subnet from given string representation
     * @param string $value
     * @return static
     * @throws SafetyCommonException
     */
    public static function parse(string $value) : static
    {
        $components = explode('/', $value);
        if (count($components) !== 2) throw new InvalidDataFormatException();

        $address = IpAddress::parse($components[0]);
        $prefix = static::parsePrefix($components[1], $address::getNumBits());

        return new static($address, $prefix);
    }


    /**
     * Parse for address block prefix
     * @param string $value
     * @param int $maxNumBits
     * @return int
     * @throws SafetyCommonException
     */
    private static function parsePrefix(string $value, int $maxNumBits) : int
    {
        if (!is_numeric($value)) throw new InvalidDataFormatException(_l('prefix must be numeric'));
        $ret = intval($value);
        if ($ret < 0 || $ret > $maxNumBits) throw new InvalidDataFormatException(_l('prefix out of range'));

        return $ret;
    }
}