<?php

namespace Magpie\General\Networks;

use Exception;
use Magpie\Exceptions\InvalidArgumentException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Objects\BinaryData;
use Magpie\System\HardCore\NumberCodecs\BinaryUint16Codec;

/**
 * Representation of an IPv6 address
 */
class Ipv6Address extends IpAddress
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'v6';
    /**
     * Expected number of groups
     */
    public const NUM_GROUPS = 8;

    /**
     * @var array<int> Underlying groups
     */
    protected readonly array $groups;


    /**
     * Constructor
     * @param iterable<int> $groups
     */
    protected function __construct(iterable $groups)
    {
        $this->groups = iter_flatten($groups, false);
    }


    /**
     * @inheritDoc
     */
    public function getBinary() : BinaryData
    {
        if (count($this->groups) !== static::NUM_GROUPS) throw new InvalidDataException();

        $ret = '';
        for ($i = 0; $i < static::NUM_GROUPS; ++$i) {
            $ret .= BinaryUint16Codec::encodeBigEndian($this->groups[$i]);
        }

        return BinaryData::fromBinary($ret);
    }


    /**
     * @inheritDoc
     */
    protected function onFormatAsString() : string
    {
        if (count($this->groups) !== static::NUM_GROUPS) throw new Exception('Wrong number of groups');

        $ret = '';
        for ($i = 0; $i < static::NUM_GROUPS; ++$i) {
            $ret .= ':' . static::formatGroup($this->groups[$i]);
        }

        $ret .= ':';

        // May try to abbreviate consecutive zeros
        $zeroPos = strpos($ret, ':0:');
        if ($zeroPos !== false) {
            $count = 1;
            $nextZeroPos = $zeroPos + 2;

            while (substr($ret, $nextZeroPos, 3) === ':0:') {
                ++$count;
                $nextZeroPos += 2;
            }

            if ($count >= 2) {
                $ret = substr($ret, 0, $zeroPos) . '::' . substr($ret, $nextZeroPos);
            }
        }

        return substr($ret, 1, -1);
    }


    /**
     * @inheritDoc
     */
    public static function getNumBits() : int
    {
        return 128;
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
        $groups = explode(':', $value);

        if (count($groups) < static::NUM_GROUPS) {
            // Insufficient number of groups, find the '' to extend
            $emptyIndex = -1;
            for ($i = 0; $i < count($groups); ++$i) {
                if ($groups[$i] === '') {
                    $emptyIndex = $i;
                    break;
                }
            }

            if ($emptyIndex < 0) throw new InvalidDataFormatException(_l('wrong number of groups'));

            while (count($groups) < static::NUM_GROUPS) {
                array_splice($groups, $emptyIndex, 0, ['']);
            }
        }

        $retGroups = [];
        foreach ($groups as $group) {
            $retGroups[] = static::parseGroup($group);
        }

        return new static($retGroups);
    }


    /**
     * Parse an IPv6 group
     * @param string $group
     * @return int
     * @throws SafetyCommonException
     */
    private static function parseGroup(string $group) : int
    {
        if ($group === '') return 0;

        // Although specification is only in lowercase, try to be more generous
        $group = strtolower($group);

        if (!ctype_xdigit($group)) throw new InvalidDataFormatException(_l('group must be hexadecimal'));

        $v = hexdec($group);
        if (is_float($v)) throw new InvalidDataFormatException(_l('overflow'));
        if ($v < 0 || $v > 65535) throw new InvalidDataFormatException(_l('group value out of range'));

        return $v;
    }


    /**
     * Format a group
     * @param int $group
     * @return string
     */
    private static function formatGroup(int $group) : string
    {
        $ret = dechex($group);
        while (str_starts_with($ret, '0') && strlen($ret) > 1) {
            $ret = substr($ret, 1);
        }

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected static function onFromBinary(string $binString) : static
    {
        if (strlen($binString) !== 16) throw new InvalidDataException();

        $retGroups = [];
        for ($i = 0; $i < 16; $i += 2) {
            $retGroups[] = BinaryUint16Codec::decodeBigEndian(substr($binString, $i, 2));
        }

        return new static($retGroups);
    }


    /**
     * Loopback (localhost) address
     * @return static
     */
    public static function loopback() : static
    {
        return new static([0, 0, 0, 0, 0, 0, 0, 1]);
    }


    /**
     * Create an IPv6 ULA of specific global ID
     * @param BinaryData|string $globalId 40-bit global ID, normally randomly generated
     * @return IpAddressSubnet
     * @throws SafetyCommonException
     */
    public static function createGlobalUlaSubnet(BinaryData|string $globalId) : IpAddressSubnet
    {
        $globalId = BinaryData::acceptBinary($globalId)->asLowerHex();
        if (strlen($globalId) !== 10) throw new InvalidArgumentException('globalId', static::formatNumberBitsReason(40));

        $ret = 'fd' . substr($globalId, 0, 2) . ':'
            . substr($globalId, 2, 4) . ':'
            . substr($globalId, 6, 4) . '::';

        $retAddress = IpAddress::parse($ret);
        return $retAddress->createSubnet(48);
    }


    /**
     * Create an IPv6 ULA of specific global ID and subnet ID
     * @param BinaryData|string $globalId
     * @param BinaryData|string|int $subnetId
     * @return IpAddressSubnet
     * @throws SafetyCommonException
     */
    public static function createSpecificUlaSubnet(BinaryData|string $globalId, BinaryData|string|int $subnetId) : IpAddressSubnet
    {
        $globalId = BinaryData::acceptBinary($globalId)->asLowerHex();
        if (strlen($globalId) !== 10) throw new InvalidArgumentException('globalId', static::formatNumberBitsReason(40));

        if (is_int($subnetId)) {
            if ($subnetId < 0 || $subnetId > 65535) throw new InvalidArgumentException('subnetId', _l('out of range'));
            $subnetId = BinaryUint16Codec::encodeBigEndian($subnetId);
        }

        $subnetId = BinaryData::acceptBinary($subnetId)->asLowerHex();
        if (strlen($subnetId) !== 4) throw new InvalidArgumentException('subnetId', static::formatNumberBitsReason(16));

        $ret = 'fd' . substr($globalId, 0, 2) . ':'
            . substr($globalId, 2, 4) . ':'
            . substr($globalId, 6, 4) . ':'
            . $subnetId . '::';

        $retAddress = IpAddress::parse($ret);
        return $retAddress->createSubnet(64);
    }


    /**
     * @param int $numBits
     * @return Localizable|string
     */
    private static function formatNumberBitsReason(int $numBits) : Localizable|string
    {
        return _format_safe(_l('must be {{0}}-bits'), $numBits)
            ?? _l('must be of specific number of bits');
    }
}