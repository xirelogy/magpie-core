<?php

namespace Magpie\Routes\Concepts;

use Magpie\Routes\RouteDiscovered;

/**
 * May specify route for discovery
 */
interface RouteSpecifiable
{
    /**
     * Discover route from given source
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public function discoverFrom(RouteDiscoverable $source) : ?RouteDiscovered;
}