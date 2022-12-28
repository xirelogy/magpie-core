<?php

namespace Magpie\General\Networks;

/**
 * IPv6 related utilities
 */
class Ipv6Utils extends IpUtilsProvider
{
    /**
     * @inheritDoc
     */
    public static function isValidAddressSubnet(string $candidate) : bool
    {
        $components = static::getSubnetComponents($candidate);
        if ($components === null) return false;

        if (!static::isValidAddress($components['address'])) return false;
        if ($components['size'] < 0 || $components['size'] > 128) return false;

        return true;

    }


    /**
     * @inheritDoc
     */
    public static function isValidAddress(string $candidate) : bool
    {
        return preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i', $candidate);
    }
}