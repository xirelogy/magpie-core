<?php

namespace Magpie\General\Networks;

use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Objects\BinaryData;

/**
 * Representation of an IPv4 address
 */
class Ipv4Address extends IpAddress
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'v4';
    /**
     * Expected number of octets
     */
    public const NUM_OCTETS = 4;

    /**
     * @var array<int> Underlying octets
     */
    protected readonly array $octets;


    /**
     * Constructor
     * @param iterable<int> $octets
     */
    protected function __construct(iterable $octets)
    {
        $this->octets = iter_flatten($octets, false);
    }


    /**
     * @inheritDoc
     */
    public function getBinary() : BinaryData
    {
        if (count($this->octets) !== static::NUM_OCTETS) throw new InvalidDataException();

        $ret = '';
        for ($i = 0; $i < static::NUM_OCTETS; ++$i) {
            $ret .= chr($this->octets[$i]);
        }

        return BinaryData::fromBinary($ret);
    }


    /**
     * @inheritDoc
     */
    protected function onFormatAsString() : string
    {
        if (count($this->octets) !== static::NUM_OCTETS) throw new Exception('Wrong number of octets');

        $ret = '';
        for ($i = 0; $i < static::NUM_OCTETS; ++$i) {
            $ret .= '.' . $this->octets[$i];
        }

        return substr($ret, 1);
    }


    /**
     * @inheritDoc
     */
    public static function getNumBits() : int
    {
        return 32;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(string $value) : static
    {
        $octets = explode('.', $value);
        if (count($octets) !== static::NUM_OCTETS) throw new InvalidDataFormatException(_l('wrong number of octets'));

        $retOctets = [];
        foreach ($octets as $octet) {
            $retOctets[] = static::parseOctet($octet);
        }

        return new static($retOctets);
    }


    /**
     * Parse an IPv4 octet to its corresponding value
     * @param string $octet
     * @return int
     * @throws SafetyCommonException
     */
    private static function parseOctet(string $octet) : int
    {
        if (!is_numeric($octet)) throw new InvalidDataFormatException(_l('octet must be numeric'));
        $octet = intval($octet);
        if ($octet < 0 || $octet > 255) throw new InvalidDataFormatException(_l('octet value out of range'));

        return $octet;
    }


    /**
     * @inheritDoc
     */
    protected static function onFromBinary(string $binString) : static
    {
        if (strlen($binString) !== 4) throw new InvalidDataException();

        $retOctets = [];
        for ($i = 0; $i < 4; ++$i) {
            $octet = ord(substr($binString, $i, 1));
            if ($octet > 255) throw new InvalidDataException();

            $retOctets[] = $octet;
        }

        return new static($retOctets);
    }


    /**
     * Loopback (localhost) address
     * @return static
     */
    public static function loopback() : static
    {
        return new static([127, 0, 0, 1]);
    }


    /**
     * Any address (Default address)
     * @return static
     */
    public static function any() : static
    {
        return new static([0, 0, 0, 0]);
    }


    /**
     * Broadcast address (Every address)
     * @return static
     */
    public static function broadcast() : static
    {
        return new static([255, 255, 255, 255]);
    }
}