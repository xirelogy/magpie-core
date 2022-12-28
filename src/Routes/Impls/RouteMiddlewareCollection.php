<?php

namespace Magpie\Routes\Impls;

use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\RouteMiddleware;
use Magpie\System\Concepts\SourceCacheTranslatable;

/**
 * Collection of route middlewares
 */
class RouteMiddlewareCollection implements SourceCacheTranslatable
{
    /**
     * @var array<string, class-string<RouteMiddleware>> Current list of middleware class names
     */
    protected array $classNames;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classNames = [];
    }


    /**
     * All middlewares in collection
     * @return iterable<class-string<RouteMiddleware>>
     */
    public function getClassNames() : iterable
    {
        foreach ($this->classNames as $className) {
            yield $className;
        }
    }


    /**
     * Create a route handler to handle routes through the middlewares
     * @param RouteHandleable $handler
     * @return RouteHandleable
     */
    public function createRouteHandler(RouteHandleable $handler) : RouteHandleable
    {
        $classNames = array_reverse($this->classNames, false);

        $ret = $handler;

        foreach ($classNames as $className) {
            if (!is_subclass_of($className, RouteMiddleware::class)) continue;
            $ret = new RouteMiddlewareHandler($className, $ret);
        }

        return $ret;
    }


    /**
     * Merge in class names
     * @param iterable<class-string<RouteMiddleware>> $classNames
     * @param bool $isAutoTally
     * @return void
     */
    public function mergeIn(iterable $classNames, bool $isAutoTally = true) : void
    {
        $isAdded = false;

        foreach ($classNames as $className) {
            if (array_key_exists($className, $this->classNames)) continue;

            $this->classNames[$className] = $className;
            $isAdded = true;
        }

        if ($isAutoTally && $isAdded) $this->tally();
    }


    /**
     * Tally the collection (sort by priority)
     * @return void
     */
    public function tally() : void
    {
        $buckets = [];
        foreach ($this->classNames as $className) {
            if (!is_subclass_of($className, RouteMiddleware::class)) continue;

            $weight = $className::getPriorityWeight();

            // Distribute into bucket
            $bucket = $buckets[$weight] ?? [];
            $bucket[] = $className;
            $buckets[$weight] = $bucket;
        }

        ksort($buckets);

        $this->classNames = [];
        foreach ($buckets as $bucket) {
            foreach ($bucket as $className) {
                $this->classNames[$className] = $className;
            }
        }
    }


    /**
     * Clone the current collection
     * @return static
     */
    public function clone() : static
    {
        $ret = new static();
        $ret->classNames = [...$this->classNames];
        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function sourceCacheExport() : array
    {
        return [...$this->classNames];
    }


    /**
     * @inheritDoc
     */
    public static function sourceCacheImport(array $data) : static
    {
        $ret = new static();
        $ret->classNames = [...$data];

        return $ret;
    }
}