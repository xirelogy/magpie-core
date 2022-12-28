<?php

namespace Magpie\Routes;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Routes\Annotations\RouteUseMiddleware;
use Magpie\Routes\Impls\RouteMap;
use Magpie\Routes\Impls\RouteMiddlewareCollection;
use Magpie\System\HardCore\AutoloadReflection;
use ReflectionException;

/**
 * Routing group
 */
abstract class RouteGroup
{
    /**
     * Prefix of the routing group
     * @return string|null
     */
    protected function getRoutePrefix() : ?string
    {
        return null;
    }


    /**
     * All directories where the related controllers are
     * @return iterable<string>
     */
    protected abstract function getControllerDirectories() : iterable;


    /**
     * All middlewares to be used
     * @return iterable<RouteUseMiddleware>
     */
    protected function getUseMiddlewares() : iterable
    {
        return [];
    }


    /**
     * Map the routes from current route group into target
     * @param RouteMap $map
     * @param RouteMiddlewareCollection $middlewares
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     * @throws UnsupportedException
     * @throws ReflectionException
     * @internal
     */
    public final function _mapTo(RouteMap $map, RouteMiddlewareCollection $middlewares) : void
    {
        $prefix = $this->getRoutePrefix();
        $paths = iter_flatten($this->getControllerDirectories());

        $groupMiddlewares = $middlewares->clone();
        $groupMiddlewares->mergeIn($this->getUseMiddlewares());

        $autoload = AutoloadReflection::instance();
        foreach ($autoload->expandDiscoverySourcesReflection($paths) as $class) {
            $map->discover($class, $groupMiddlewares, $prefix);
        }
    }


    /**
     * Create group from simple routing group
     * @param iterable<string> $directories
     * @param string|null $prefix
     * @param array<class-string<RouteMiddleware>> $useMiddlewares
     * @return static
     */
    public static function fromSimple(iterable $directories, ?string $prefix = null, array $useMiddlewares = []) : static
    {
        $directories = iter_flatten($directories, false);

        return new class($directories, $prefix, $useMiddlewares) extends RouteGroup {
            /**
             * Constructor
             * @param array<string> $directories
             * @param string|null $prefix
             * @param array<class-string<RouteMiddleware>> $useMiddlewares
             */
            public function __construct(
                protected array $directories,
                protected ?string $prefix,
                protected array $useMiddlewares,
            ) {

            }


            /**
             * @inheritDoc
             */
            protected function getRoutePrefix() : ?string
            {
                return $this->prefix;
            }


            /**
             * @inheritDoc
             */
            protected function getControllerDirectories() : iterable
            {
                return $this->directories;
            }


            /**
             * @inheritDoc
             */
            protected function getUseMiddlewares() : iterable
            {
                return $this->useMiddlewares;
            }
        };
    }
}