<?php

namespace Magpie\Routes;

use Exception;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\Concepts\UserCollectable;
use Magpie\HttpServer\Exceptions\HttpNotFoundException;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Handlers\ClosureRouteHandler;
use Magpie\Routes\Handlers\ControllerMethodRouteHandler;
use Magpie\Routes\Handlers\MethodFallbackRouteHandler;
use Magpie\Routes\Impls\ActualRouteContext;
use Magpie\Routes\Impls\RouteInfo;
use Magpie\Routes\Impls\RouteLanding;
use Magpie\Routes\Impls\RouteMap;
use Magpie\Routes\Impls\RouteMiddlewareCollection;
use Magpie\System\HardCore\SourceCache;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

/**
 * Routing domain
 */
abstract class RouteDomain
{
    /**
     * @var string|null The associated route domain / domain specification
     */
    public readonly ?string $domain;
    /**
     * @var array<string> Alternative domains that should be routed to this domain
     */
    public readonly array $altDomains;
    /**
     * @var bool If current domain is booted up
     */
    private bool $isBoot = false;
    /**
     * @var RouteMiddlewareCollection Associated middlewares for the entire domain
     */
    private RouteMiddlewareCollection $middlewares;
    /**
     * @var RouteMap|null Routing map
     */
    private ?RouteMap $map = null;


    /**
     * Constructor
     * @param string|null $domain
     * @param string ...$altDomains
     */
    protected function __construct(?string $domain = null, string ...$altDomains)
    {
        $this->domain = $domain !== null ? trim($domain) : null;
        $this->altDomains = $altDomains;
        $this->middlewares = new RouteMiddlewareCollection();
    }


    /**
     * Get the route for given class-method combination
     * @param string $className
     * @param string $methodName
     * @return RouteDiscovered|null
     */
    public final function routeOf(string $className, string $methodName) : ?RouteDiscovered
    {
        $url = $this->discoverRouteOf($className, $methodName);
        if ($url === null) return null;

        return new class($this->domain ?? '', $url) extends RouteDiscovered {
            /**
             * Constructor
             * @param string $hostname
             * @param string $url
             */
            public function __construct(string $hostname, string $url)
            {
                parent::__construct($hostname, $url);
            }
        };
    }


    /**
     * Get route from discovery
     * @param string $className
     * @param string $methodName
     * @return string|null
     */
    private function discoverRouteOf(string $className, string $methodName) : ?string
    {
        try {
            $class = new ReflectionClass($className);
            $prefix = RouteMap::findRoutePrefixFromAttribute($class);

            $method = $class->getMethod($methodName);
            $entry = RouteMap::findRouteEntryFromAttribute($method);
            if ($entry === null) return null;

            return RouteMap::combineRoutes($prefix?->path, $entry->path);
        } catch (Exception) {
            return null;
        }
    }


    /**
     * All directories where the related controllers are
     * @return iterable<string>
     */
    protected abstract function getControllerDirectories() : iterable;


    /**
     * All routing groups
     * @return iterable<RouteGroup>
     */
    protected function getGroups() : iterable
    {
        return [];
    }


    /**
     * All middlewares to be used
     * @return iterable<class-string<RouteMiddleware>>
     */
    protected function getUseMiddlewares() : iterable
    {
        return [];
    }


    /**
     * Get method fallback route handler
     * @param Request $request
     * @return RouteHandleable
     */
    protected function getMethodFallbackRouteHandler(Request $request) : RouteHandleable
    {
        _used($request);

        return new MethodFallbackRouteHandler();
    }


    /**
     * Get fallback route handler
     * @return RouteHandleable
     */
    protected function getFallbackRouteHandler() : RouteHandleable
    {
        return ClosureRouteHandler::for(function (Request $request) : mixed {
            _used($request);
            throw new HttpNotFoundException();
        });
    }


    /**
     * Get client addresses resolver specific for this domain, if any
     * @return ClientAddressesResolvable|null
     */
    protected function getClientAddressesResolver() : ?ClientAddressesResolvable
    {
        return null;
    }


    /**
     * Get client addresses resolver specific for this domain, if any
     * @return ClientAddressesResolvable|null
     * @internal
     */
    public final function _getClientAddressesResolver() : ?ClientAddressesResolvable
    {
        return $this->getClientAddressesResolver();
    }


    /**
     * Get a handler for given route
     * @param Request $request
     * @return RouteHandleable
     * @throws SafetyCommonException
     * @throws ReflectionException
     * @internal
     */
    public final function _route(Request $request) : RouteHandleable
    {
        $routeContext = $request->routeContext instanceof ActualRouteContext ? $request->routeContext : null;

        // Ensure boot up
        $this->ensureBoot();

        // Associate this request with given route domain
        $request->routeDomain = $this;

        // Resolve from landing map
        $landingMap = $this->map->land($request->requestUri->path, $routeArguments);
        if ($landingMap !== null) {
            $routeContext?->_setLandingMap($landingMap);
            $requestMethod = $request->getMethod();
            if (array_key_exists($requestMethod, $landingMap)) {
                // Landing ok
                $landing = $landingMap[$requestMethod];
                return static::landRouteHandler($request, $routeArguments, $landing, $this->map);
            } else {
                // Not landed

                // 'HEAD' may be handled accordingly
                // Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
                if ($requestMethod === CommonHttpMethod::HEAD && array_key_exists(CommonHttpMethod::GET, $landingMap)) {
                    $getLanding = $landingMap[CommonHttpMethod::GET];
                    return static::landRouteHandler($request, $routeArguments, $getLanding, $this->map);
                }

                // Otherwise, fallback
                $handler = $this->getMethodFallbackRouteHandler($request);
                return $this->middlewares->createRouteHandler($handler);
            }
        }

        // Fallback when no route is matched
        $fallbackHandler = $this->getFallbackRouteHandler();
        return $this->middlewares->createRouteHandler($fallbackHandler);
    }


    /**
     * All active routes
     * @return iterable<RouteInfo>
     * @throws SafetyCommonException
     * @throws ReflectionException
     * @internal
     */
    public final function _all() : iterable
    {
        // Ensure boot up
        $this->ensureBoot();

        yield from $this->map->all($this->domain);
    }


    /**
     * Export for source cache
     * @return array
     * @throws Exception
     * @internal
     */
    public final function _sourceCacheExport() : array
    {
        $this->ensureBoot();

        return [
            'middlewares' => $this->middlewares->sourceCacheExport(),
            'map' => $this->map?->sourceCacheExport(),
        ];
    }


    /**
     * Remove boot up status
     * @return void
     * @internal
     */
    public final function _unboot() : void
    {
        $this->isBoot = false;
        $this->middlewares = new RouteMiddlewareCollection();
        $this->map = null;
    }


    /**
     * Land into a route handler
     * @param Request $request
     * @param array $routeArguments
     * @param RouteLanding $landing
     * @param RouteMap|null $map
     * @return RouteHandleable
     * @throws OperationFailedException
     */
    private static function landRouteHandler(Request $request, array $routeArguments, RouteLanding $landing, ?RouteMap $map) : RouteHandleable
    {
        $request->routeArguments = static::createRouteArgumentsCollection($landing->argumentNames, $routeArguments);

        $handler = $landing->createHandler();
        if ($handler === null) throw new OperationFailedException();

        if ($handler instanceof ControllerMethodRouteHandler) {
            $routeVariables = $map?->getRouteVariables($handler->controllerClassName);
            if ($routeVariables !== null) {
                $request->routeContext->_setRouteVariables($routeVariables);
            }
        }

        return $landing->middlewares->createRouteHandler($handler);
    }


    /**
     * Ensure that current domain is boot up
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     * @throws UnsupportedException
     * @throws ReflectionException
     */
    protected final function ensureBoot() : void
    {
        if ($this->isBoot) return;
        $this->isBoot = true;

        if ($this->loadFromSourceCache()) return;

        $middlewares = new RouteMiddlewareCollection();
        $middlewares->mergeIn($this->getUseMiddlewares());
        $this->middlewares = $middlewares;

        $this->map = RouteMap::from($this->getControllerDirectories(), $middlewares);

        foreach ($this->getGroups() as $group) {
            $group->_mapTo($this->map, $middlewares);
        }
    }


    /**
     * Try to load from source cache
     * @return bool
     */
    private function loadFromSourceCache() : bool
    {
        try {
            $cached = SourceCache::instance()->getCache(static::class);
            if ($cached === null) return false;

            $this->middlewares = RouteMiddlewareCollection::sourceCacheImport($cached['middlewares']);
            $mapData = $cached['map'] ?? null;
            if ($mapData !== null) $this->map = RouteMap::sourceCacheImport($mapData);

            return true;
        } catch (Exception) {
            return false;
        }
    }


    /**
     * Create route arguments collection
     * @param array $argumentNames
     * @param array $argumentValues
     * @return UserCollectable
     */
    private static function createRouteArgumentsCollection(array $argumentNames, array $argumentValues) : UserCollectable
    {
        $arr = array_combine($argumentNames, $argumentValues);
        return Request::_createRouteArgumentsCollectionFrom($arr);
    }
}