<?php

namespace Magpie\Routes\Impls;

use Magpie\Routes\RouteContext;

/**
 * Actual route context instance
 * @internal
 */
class ActualRouteContext extends RouteContext
{
    /**
     * @var array<string, RouteLanding>|null Associated landing map
     */
    protected ?array $landingMap = null;
    /**
     * @var array<string, mixed> Route variables
     */
    protected array $routeVariables = [];


    /**
     * Assign the landing map
     * @param array<string, RouteLanding> $landingMap
     * @return void
     * @internal
     */
    public function _setLandingMap(array $landingMap) : void
    {
        $this->landingMap = $landingMap;
    }


    /**
     * @inheritDoc
     */
    public function getAllowedMethods() : ?array
    {
        if ($this->landingMap === null) return null;

        return array_keys($this->landingMap);
    }


    /**
     * Set route variables
     * @param array $variables
     * @return void
     * @internal
     */
    public function _setRouteVariables(array $variables) : void
    {
        $this->routeVariables = $variables;
    }


    /**
     * @inheritDoc
     */
    public function getRouteVariable(string $name, mixed $default = null) : mixed
    {
        return $this->routeVariables[$name] ?? $default;
    }
}