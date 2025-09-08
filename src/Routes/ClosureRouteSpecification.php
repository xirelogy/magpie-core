<?php

namespace Magpie\Routes;

use Closure;
use Magpie\Routes\Concepts\RouteDiscoverable;
use Magpie\Routes\Concepts\RouteSpecifiable;

/**
 * Route specification from closure/callable
 */
class ClosureRouteSpecification implements RouteSpecifiable
{
    /**
     * @var Closure Target closure/callable
     */
    public readonly Closure $fn;


    /**
     * Constructor
     * @param Closure $fn
     */
    public function __construct(Closure $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function discoverFrom(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOfCallable($this->fn);
    }
}