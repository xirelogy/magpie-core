<?php

namespace Magpie\Routes\Impls;

use Magpie\Controllers\Controller;
use Magpie\General\Sugars\Excepts;
use Magpie\General\TrackedArray;
use Magpie\General\Traits\SingletonInstance;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteControllerMethodHookEventable;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Concepts\RouteHandledEventable;
use Magpie\Routes\Concepts\RouteHookEventable;

/**
 * Receive and process events for routing
 * @internal
 */
class RouteEventHost
{
    use SingletonInstance;

    /**
     * Prefix for route handled
     */
    protected const PREFIX_ROUTE_HANDLED = 'RouteHandled::';
    /**
     * Prefix for route hook
     */
    protected const PREFIX_ROUTE_HOOK = 'RouteHook::';
    /**
     * Prefix for controller-method route hook
     */
    protected const PREFIX_ROUTE_CONTROLLER_METHOD_HOOK = 'RouteControllerMethodHook::';

    /**
     * @var TrackedArray<RouteHandledEventable> May handle route handler determined events
     */
    protected TrackedArray $routeHandledEvents;
    /**
     * @var TrackedArray<RouteHookEventable> May handle route hook (before/after) of a route
     */
    protected TrackedArray $routeHookEvents;
    /**
     * @var TrackedArray<RouteControllerMethodHookEventable> May handle route hook (before/after) of a controller-method route
     */
    protected TrackedArray $routeControllerMethodHookEvents;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->routeHandledEvents = new TrackedArray();
        $this->routeHookEvents = new TrackedArray();
        $this->routeControllerMethodHookEvents = new TrackedArray();
    }


    /**
     * Subscribe to route handler determined
     * @param RouteHandledEventable $eventHandle
     * @return string Corresponding key to the handler
     */
    public final function subscribeRouteHandled(RouteHandledEventable $eventHandle) : string
    {
        $ret = $this->routeHandledEvents->add($eventHandle);
        return static::PREFIX_ROUTE_HANDLED . $ret;
    }


    /**
     * Get notified of a route handler determined
     * @param Request $request
     * @param RouteHandleable $handler
     * @param RouteLanding|null $landing
     * @return void
     */
    public final function notifyRouteHandled(Request $request, RouteHandleable $handler, ?RouteLanding $landing = null) : void
    {
        _used($landing);

        foreach ($this->routeHandledEvents->getItems() as $item) {
            Excepts::noThrow(fn () => $item->notifyRouteHandled($request, $handler));
        }
    }


    /**
     * Subscribe to route hook (before/after)
     * @param RouteHookEventable $eventHandle
     * @return string
     */
    public final function subscribeRouteHook(RouteHookEventable $eventHandle) : string
    {
        $ret = $this->routeHookEvents->add($eventHandle);
        return static::PREFIX_ROUTE_HOOK . $ret;
    }


    /**
     * Get notified of a route will be starting
     * @param RouteHandleable $handler
     * @param Request $request
     * @return void
     */
    public final function notifyBeforeRoute(RouteHandleable $handler, Request $request) : void
    {
        foreach ($this->routeHookEvents->getItems() as $item) {
            Excepts::noThrow(fn () => $item->notifyBeforeRoute($handler, $request));
        }
    }


    /**
     * Get notified of a route has response
     * @param RouteHandleable $handler
     * @param mixed $response
     * @return void
     */
    public final function notifyAfterRoute(RouteHandleable $handler, mixed $response) : void
    {
        foreach ($this->routeHookEvents->getItems() as $item) {
            Excepts::noThrow(fn () => $item->notifyAfterRoute($handler, $response));
        }
    }


    /**
     * Subscribe to controller-method route hook (before/after)
     * @param RouteHookEventable $eventHandle
     * @return string
     */
    public final function subscribeControllerMethodHook(RouteHookEventable $eventHandle) : string
    {
        $ret = $this->routeControllerMethodHookEvents->add($eventHandle);
        return static::PREFIX_ROUTE_CONTROLLER_METHOD_HOOK . $ret;
    }


    /**
     * Get notified of a controller method in route will be starting
     * @param class-string<Controller> $controllerClass
     * @param string $methodName
     * @param Request $request
     * @param array $routeArguments
     * @return void
     */
    public final function notifyBeforeControllerMethod(string $controllerClass, string $methodName, Request $request, array $routeArguments) : void
    {
        foreach ($this->routeControllerMethodHookEvents->getItems() as $item) {
            Excepts::noThrow(fn () => $item->notifyBeforeControllerMethod($controllerClass, $methodName, $request, $routeArguments));
        }
    }


    /**
     * Get notified of a controller method in route has initial (raw) response
     * @param class-string<Controller> $controllerClass
     * @param string $methodName
     * @param mixed $response
     * @return void
     */
    public final function notifyAfterControllerMethod(string $controllerClass, string $methodName, mixed $response) : void
    {
        foreach ($this->routeControllerMethodHookEvents->getItems() as $item) {
            Excepts::noThrow(fn () => $item->notifyAfterControllerMethod($controllerClass, $methodName, $response));
        }
    }


    /**
     * Unsubscribe an event
     * @param string $key
     * @return bool
     */
    public function unsubscribe(string $key) : bool
    {
        if (static::checkKeyPrefix($key, static::PREFIX_ROUTE_HANDLED)) {
            return $this->routeHandledEvents->remove($key);
        }

        if (static::checkKeyPrefix($key, static::PREFIX_ROUTE_HOOK)) {
            return $this->routeHookEvents->remove($key);
        }

        if (static::checkKeyPrefix($key, static::PREFIX_ROUTE_CONTROLLER_METHOD_HOOK)) {
            return $this->routeControllerMethodHookEvents->remove($key);
        }

        return false;
    }


    /**
     * Check if the given key is of target prfix
     * @param string $key
     * @param string $prefix
     * @return bool
     */
    protected static function checkKeyPrefix(string &$key, string $prefix) : bool
    {
        if (!str_starts_with($key, $prefix)) return false;
        $key = substr($key, strlen($prefix));
        return true;
    }
}