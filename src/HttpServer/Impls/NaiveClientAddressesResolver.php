<?php

namespace Magpie\HttpServer\Impls;

use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\ServerCollection;

/**
 * Naive implementation of ClientAddressesResolvable
 * @internal
 */
class NaiveClientAddressesResolver implements ClientAddressesResolvable
{
    /**
     * @inheritDoc
     */
    public function resolveFrom(string $directAddress, ServerCollection $serverVars) : iterable
    {
        if (trim($directAddress) !== '') yield $directAddress;
    }
}