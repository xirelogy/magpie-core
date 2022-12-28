<?php

namespace Magpie\HttpServer\Concepts;

use Magpie\HttpServer\ServerCollection;

/**
 * Implementation to resolve for client's addresses
 */
interface ClientAddressesResolvable
{
    /**
     * Resolve for all client addresses using given direct address and server variables.
     * As a convention, the most relevant address shall be always returned first
     * @param string $directAddress
     * @param ServerCollection $serverVars
     * @return iterable<string>
     */
    public function resolveFrom(string $directAddress, ServerCollection $serverVars) : iterable;
}