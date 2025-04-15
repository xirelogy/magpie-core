<?php

namespace Magpie\Routes\Impls;

use Magpie\Routes\RouteDiscovered;

/**
 * An actual (simple) discovered route
 * @internal
 */
class ActualRouteDiscovered extends RouteDiscovered
{
    /**
     * Constructor
     * @param string $hostname
     * @param string $url
     */
    public function __construct(string $hostname, string $url)
    {
        parent::__construct($hostname, $url);
    }
}