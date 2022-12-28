<?php

namespace Magpie\General\Networks;

/**
 * IP related utilities
 */
class IpUtils extends IpUtilsProvider
{
    /**
     * @inheritDoc
     */
    public static function isValidAddressSubnet(string $candidate) : bool
    {
        /** @var class-string<IpUtilsProvider> $providerClass */
        foreach (static::getProviderClasses() as $providerClass) {
            if ($providerClass::isValidAddressSubnet($candidate)) return true;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public static function isValidAddress(string $candidate) : bool
    {
        /** @var class-string<IpUtilsProvider> $providerClass */
        foreach (static::getProviderClasses() as $providerClass) {
            if ($providerClass::isValidAddress($candidate)) return true;
        }

        return false;
    }


    /**
     * All providers
     * @return iterable<class-string<IpUtilsProvider>>
     */
    protected static function getProviderClasses() : iterable
    {
        yield Ipv4Utils::class;
        yield Ipv6Utils::class;
    }
}