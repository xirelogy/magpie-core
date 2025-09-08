<?php

namespace Magpie\Routes;

use Magpie\Controllers\Controller;
use Magpie\Routes\Concepts\RouteDiscoverable;
use Magpie\Routes\Concepts\RouteSpecifiable;

/**
 * Route specification from class and method
 */
class ClassMethodRouteSpecification implements RouteSpecifiable
{
    /**
     * @var class-string<Controller> Controller class
     */
    public readonly string $className;
    /**
     * @var string Method name
     */
    public readonly string $methodName;


    /**
     * Constructor
     * @param string $className
     * @param string $methodName
     */
    public function __construct(string $className, string $methodName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
    }


    /**
     * @inheritDoc
     */
    public function discoverFrom(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf($this->className, $this->methodName);
    }
}