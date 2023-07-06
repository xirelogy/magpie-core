<?php

namespace Magpie\HttpServer\Resolvers;

use Magpie\General\Traits\StaticCreatable;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\ServerCollection;

/**
 * Default client address resolver: only consider the directly visible remote address
 */
class DefaultClientAddressesResolver implements ClientAddressesResolvable
{
    use StaticCreatable;

    /**
     * @inheritDoc
     */
    public function resolveFrom(string $directAddress, ServerCollection $serverVars) : iterable
    {
        if (trim($directAddress) !== '') yield $directAddress;
    }
}