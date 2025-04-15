<?php

namespace Magpie\Routes;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Facades\Random;
use Magpie\General\LazyArray;
use Magpie\General\Randoms\RandomCharset;
use Magpie\Routes\Concepts\RouteDiscoverable;
use Magpie\Routes\Impls\ActualRouteDiscovered;
use Magpie\Routes\Impls\RouteMap;
use Magpie\Routes\Impls\RouteMiddlewareCollection;
use Magpie\Routes\Traits\CommonRouteOfCallable;
use Magpie\System\HardCore\AutoloadReflection;
use ReflectionException;

/**
 * Routing group
 */
abstract class RouteGroup implements RouteDiscoverable
{
    use CommonRouteOfCallable;

    /**
     * @var string ID for current routing group
     */
    public readonly string $id;
    /**
     * @var string|null Specific domain
     */
    private ?string $domain = null;


    /**
     * Constructor
     * @param string|null $id Specific ID for current routing group, otherwise randomly generated
     */
    protected function __construct(?string $id = null)
    {
        $this->id = $id ?? Random::string(8, RandomCharset::LOWER_ALPHANUM);
    }


    /**
     * Prefix of the routing group
     * @return string|null
     */
    protected function getRoutePrefix() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public final function routeOf(string $className, string $methodName) : ?RouteDiscovered
    {
        $url = RouteMap::getRouteOf($className, $methodName);
        if ($url === null) return null;

        return new ActualRouteDiscovered($this->domain ?? '', ($this->getRoutePrefix() ?? '') . $url);
    }


    /**
     * All directories where the related controllers are
     * @return iterable<string>
     */
    protected abstract function getControllerDirectories() : iterable;


    /**
     * All middlewares to be used
     * @return iterable<class-string<RouteMiddleware>>
     */
    protected function getUseMiddlewares() : iterable
    {
        return [];
    }


    /**
     * All route variables set
     * @return iterable<string, mixed>
     */
    protected function getSetVariables() : iterable
    {
        return [];
    }


    /**
     * Map the routes from current route group into target
     * @param string|null $domain
     * @param RouteMap $map
     * @param RouteMiddlewareCollection $middlewares
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     * @throws UnsupportedException
     * @throws ReflectionException
     * @internal
     */
    public final function _mapTo(?string $domain, RouteMap $map, RouteMiddlewareCollection $middlewares) : void
    {
        $this->domain = $domain;

        $prefix = $this->getRoutePrefix();
        $paths = iter_flatten($this->getControllerDirectories());

        $groupMiddlewares = $middlewares->clone();
        $groupMiddlewares->mergeIn($this->getUseMiddlewares());

        $setVariables = iter_flatten($this->getSetVariables());

        $autoload = AutoloadReflection::instance();
        foreach ($autoload->expandDiscoverySourcesReflection($paths) as $class) {
            $map->discover($class, $groupMiddlewares, $setVariables, $prefix, $this->id);
        }
    }


    /**
     * Create group from simple routing group
     * @param iterable<string> $directories
     * @param string|null $prefix
     * @param iterable<class-string<RouteMiddleware>> $useMiddlewares
     * @param iterable<string, mixed> $setVariables
     * @param string|null $id
     * @return static
     */
    public static function fromSimple(iterable $directories, ?string $prefix = null, iterable $useMiddlewares = [], iterable $setVariables = [], ?string $id = null) : static
    {
        $directories = new LazyArray($directories);
        $useMiddlewares = new LazyArray($useMiddlewares);
        $setVariables = iter_flatten($setVariables);

        return new class($directories, $prefix, $useMiddlewares, $setVariables, $id) extends RouteGroup {
            /**
             * Constructor
             * @param LazyArray<string> $directories
             * @param string|null $prefix
             * @param LazyArray<class-string<RouteMiddleware>> $useMiddlewares
             * @param array<string, mixed> $setVariables
             * @param string|null $id
             */
            public function __construct(
                protected LazyArray $directories,
                protected ?string $prefix,
                protected LazyArray $useMiddlewares,
                protected array $setVariables,
                ?string $id,
            ) {
                parent::__construct($id);
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
                yield from $this->directories->getItems();
            }


            /**
             * @inheritDoc
             */
            protected function getUseMiddlewares() : iterable
            {
                yield from $this->useMiddlewares->getItems();
            }


            /**
             * @inheritDoc
             */
            protected function getSetVariables() : iterable
            {
                yield from $this->setVariables;
            }
        };
    }
}