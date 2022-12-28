<?php

namespace Magpie\Routes;

use Exception;
use Magpie\General\Traits\StaticCreatable;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Constants\RouteMiddlewarePriority;

/**
 * Route middleware
 */
abstract class RouteMiddleware
{
    use StaticCreatable;


    /**
     * Handle the route at current level
     * @param Request $request
     * @param RouteHandleable $next
     * @return mixed
     * @throws Exception
     */
    public abstract function handle(Request $request, RouteHandleable $next) : mixed;


    /**
     * The priority weight for sorting, the lower the number, the higher the priority
     * @return int
     */
    public static function getPriorityWeight() : int
    {
        return RouteMiddlewarePriority::DEFAULT;
    }
}