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
     * @var ForwardingUserCollection Domain arguments (forwarded)
     */
    protected readonly ForwardingUserCollection $domainArguments;
    /**
     * @var ForwardingUserCollection Route arguments (forwarded)
     */
    protected readonly ForwardingUserCollection $routeArguments;
    /**
     * @var array<string, RouteLanding>|null Associated landing map
     */
    protected ?array $landingMap = null;
    /**
     * @var array<string, mixed> Route variables
     */
    protected array $routeVariables = [];


    /**
     * Constructor
     * @param ForwardingUserCollection $domainArguments
     * @param ForwardingUserCollection $routeArguments
     */
    protected function __construct(ForwardingUserCollection $domainArguments, ForwardingUserCollection $routeArguments)
    {
        $this->domainArguments = $domainArguments;
        $this->routeArguments = $routeArguments;
    }


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


    /**
     * Update domain arguments
     * @param iterable<string, mixed> $vars
     * @return void
     * @internal
     */
    public function _setDomainArguments(iterable $vars) : void
    {
        $this->domainArguments->_reconfigure($vars);
    }


    /**
     * Update route arguments
     * @param iterable<string, mixed> $vars
     * @return void
     * @internal
     */
    public function _setRouteArguments(iterable $vars) : void
    {
        $this->routeArguments->_reconfigure($vars);
    }


    /**
     * Create an instance
     * @param ForwardingUserCollection $domainArguments
     * @param ForwardingUserCollection $routeArguments
     * @return static
     * @internal
     */
    public static function _create(ForwardingUserCollection $domainArguments, ForwardingUserCollection $routeArguments) : static
    {
        return new static($domainArguments, $routeArguments);
    }
}