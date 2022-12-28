<?php

namespace Magpie\General\Networks;

/**
 * IPv4 related utilities
 */
class Ipv4Utils extends IpUtilsProvider
{
    /**
     * @inheritDoc
     */
    public static function isValidAddressSubnet(string $candidate) : bool
    {
        $components = static::getSubnetComponents($candidate);
        if ($components === null) return false;

        if (!static::isValidAddress($components['address'])) return false;
        if ($components['size'] < 0 || $components['size'] > 32) return false;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function isValidAddress(string $candidate) : bool
    {
        return preg_match('/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/', $candidate);
    }
}