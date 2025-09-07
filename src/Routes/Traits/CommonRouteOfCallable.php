<?php

namespace Magpie\Routes\Traits;

use Closure;
use Magpie\General\Sugars\Excepts;
use Magpie\Routes\RouteDiscovered;
use ReflectionFunction;

/**
 * Common implementation for routeOfCallable()
 */
trait CommonRouteOfCallable
{
    /**
     * Get the route for given callable
     * @param Closure $fn
     * @return RouteDiscovered|null
     */
    public final function routeOfCallable(Closure $fn) : ?RouteDiscovered
    {
        return Excepts::noThrow(function () use ($fn) {
            $reflection = new ReflectionFunction($fn);

            $methodName = $reflection->name;
            if (str_contains($methodName, '{closure}')) return null;
            $className = $reflection->getClosureCalledClass()?->name;
            if ($className === null) return null;

            return $this->routeOf($className, $methodName);
        });
    }
}