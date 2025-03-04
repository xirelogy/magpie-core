<?php

namespace Magpie\Routes;

/**
 * Route related context
 */
abstract class RouteContext
{
    /**
     * All allowed methods (if available)
     * @return array<string>|null
     */
    public abstract function getAllowedMethods() : ?array;


    /**
     * Get route variable (set by attributes reflection)
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public abstract function getRouteVariable(string $name, mixed $default = null) : mixed;


    /**
     * Associated routing group ID, if available
     * @return string|null
     */
    public abstract function getRouteGroupId() : ?string;
}