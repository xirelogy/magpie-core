<?php

namespace Magpie\HttpServer\Resolvers;

use Magpie\General\Networks\IpAddress;
use Magpie\General\Networks\IpAddressSubnet;
use Magpie\General\Sugars\Excepts;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\ServerCollection;

/**
 * Client address resolver that accepts forwarding proxies if they are trusted
 */
abstract class TrustForwardClientAddressesResolver implements ClientAddressesResolvable
{
    /**
     * @var array<IpAddressSubnet>|null Trusted subnets
     */
    private ?array $cachedTrustedSubnets = null;


    /**
     * @inheritDoc
     */
    public final function resolveFrom(string $directAddress, ServerCollection $serverVars) : iterable
    {
        $ret = $this->onResolveFrom($directAddress, $serverVars);

        yield from $ret->finalize();
    }


    /**
     * Resolve into an address chain
     * @param string $directAddress
     * @param ServerCollection $serverVars
     * @return AddressChain
     */
    private function onResolveFrom(string $directAddress, ServerCollection $serverVars) : AddressChain
    {
        $ret = new AddressChain();

        // Handle the direct address
        if (!$ret->push($directAddress)) return $ret;
        if (!$this->isAddressTrusted($ret->getLastAddress())) return $ret;

        $headers = $serverVars->getHeaders();

        // Process X-Real-IP
        $realIp = $headers->safeOptional('X-Real-IP');
        if ($ret->push($realIp)) {
            if (!$this->isAddressTrusted($ret->getLastAddress())) return $ret;
        }

        // Process X-Forwarded-For in reverse order
        // @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
        $forwardedFor = $headers->safeOptional('X-Forwarded-For');
        if ($forwardedFor !== null) {
            $forwardedAddresses = array_reverse(explode(',', $forwardedFor));
            foreach ($forwardedAddresses as $forwardedAddress) {
                $forwardedAddress = trim($forwardedAddress);
                if (!$ret->push($forwardedAddress)) return $ret;
                if (!$this->isAddressTrusted($ret->getLastAddress())) return $ret;
            }
        }

        return $ret;
    }


    /**
     * Check if address trusted
     * @param IpAddress|null $address
     * @return bool
     */
    protected final function isAddressTrusted(?IpAddress $address) : bool
    {
        if ($address === null) return false;

        foreach ($this->getCachedTrustedSubnets() as $subnet) {
            if ($subnet->address::getTypeClass() != $address::getTypeClass()) continue;

            if (Excepts::noThrow(fn () => $subnet->isAddressInRange($address), false)) return true;
        }

        return false;
    }


    /**
     * Trusted subnets (use cache if available)
     * @return array<IpAddressSubnet>
     */
    private function getCachedTrustedSubnets() : array
    {
        if ($this->cachedTrustedSubnets === null) {
            $this->cachedTrustedSubnets = [];
            foreach ($this->getTrustedSubnets() as $subnet) {
                $subnet = static::acceptSubnet($subnet);
                if ($subnet === null) continue;
                $this->cachedTrustedSubnets[] = $subnet;
            }
        }

        return $this->cachedTrustedSubnets;
    }


    /**
     * All trusted subnets
     * @return iterable<IpAddressSubnet|string>
     */
    protected abstract function getTrustedSubnets() : iterable;


    /**
     * Accept various subnet specification
     * @param IpAddressSubnet|string $subnet
     * @return IpAddressSubnet|null
     */
    private static function acceptSubnet(IpAddressSubnet|string $subnet) : ?IpAddressSubnet
    {
        if ($subnet instanceof IpAddressSubnet) return $subnet;
        return Excepts::noThrow(fn () => IpAddressSubnet::parse($subnet));
    }
}