<?php

namespace Magpie\Routes;

use Exception;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\General\Traits\StaticCreatable;
use Magpie\HttpServer\Concepts\WithHeaderSpecifiable;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\Response;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Constants\RouteMiddlewarePriority;
use Stringable;

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


    /**
     * Try to cast the response to make it header specifiable
     * @param mixed $response
     * @return WithHeaderSpecifiable|mixed
     */
    protected static function tryCastResponseAsHeaderSpecifiable(mixed $response) : mixed
    {
        if ($response instanceof WithHeaderSpecifiable) return $response;
        if ($response === null) return new Response('', CommonHttpStatusCode::NO_CONTENT);

        // Support for string as HTML content
        if (is_string($response)) return new Response($response);
        if ($response instanceof Stringable) return new Response($response->__toString());

        // Otherwise, 'as-is'
        return $response;
    }
}