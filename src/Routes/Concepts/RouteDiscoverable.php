<?php

namespace Magpie\Routes\Concepts;

use Closure;
use Magpie\Controllers\Controller;
use Magpie\Routes\RouteDiscovered;

/**
 * Anything that could discover a route
 */
interface RouteDiscoverable
{
    /**
     * Get the route for given class-method combination
     * @param class-string<Controller> $className
     * @param string $methodName
     * @return RouteDiscovered|null
     */
    public function routeOf(string $className, string $methodName) : ?RouteDiscovered;


    /**
     * Get the route for given callable
     * @param Closure $fn
     * @return RouteDiscovered|null
     */
    public function routeOfCallable(Closure $fn) : ?RouteDiscovered;
}