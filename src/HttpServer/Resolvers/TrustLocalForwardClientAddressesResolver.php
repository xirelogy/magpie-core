<?php

namespace Magpie\HttpServer\Resolvers;

use Magpie\General\Traits\StaticCreatable;

/**
 * Client address resolver that accepts forwarding proxies if they are trusted - for all valid local addresses
 * subnets
 */
class TrustLocalForwardClientAddressesResolver extends TrustForwardClientAddressesResolver
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    protected function getTrustedSubnets() : iterable
    {
        // IPv4 loopback addresses
        yield '127.0.0.0/8';

        // IPv4 private networks
        yield '10.0.0.0/8';
        yield '172.16.0.0/12';
        yield '192.168.0.0/16';

        // IPv6 loopback address
        yield '::1';

        // IPv6 private networks and ULA
        yield 'fc00::/7'; // ULA covers 'fd00::/8'
    }
}