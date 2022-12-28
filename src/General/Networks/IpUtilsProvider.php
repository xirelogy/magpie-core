<?php

namespace Magpie\General\Networks;

use Magpie\General\Str;
use Magpie\General\Traits\StaticClass;

/**
 * Common functionalities for IP related utilities
 */
abstract class IpUtilsProvider
{
    use StaticClass;


    /**
     * Check if given candidate is valid subnet specification
     * @param string $candidate
     * @return bool
     */
    public abstract static function isValidAddressSubnet(string $candidate) : bool;


    /**
     * Check if given candidate is valid address
     * @param string $candidate
     * @return bool
     */
    public abstract static function isValidAddress(string $candidate) : bool;


    /**
     * Convert slash notation into their corresponding components if it is valid
     * @param string $value
     * @return array{address: string, size: int}|null
     */
    protected static function getSubnetComponents(string $value) : ?array
    {
        $items = explode('/', $value);
        if (count($items) != 2) return null;
        if (!Str::isInteger($items[1])) return null;

        return [
            'address' => $items[0],
            'size' => intval($items[1]),
        ];
    }
}