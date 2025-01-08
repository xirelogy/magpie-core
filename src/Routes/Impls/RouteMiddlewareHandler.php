<?php

namespace Magpie\Routes\Impls;

use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\RouteMiddleware;

/**
 * Wrap middleware as a route handle
 * @internal
 */
class RouteMiddlewareHandler implements RouteHandleable
{
    /**
     * @var class-string<RouteMiddleware> Currently associated middleware
     */
    protected string $middlewareClassName;
    /**
     * @var RouteHandleable Next level
     */
    protected RouteHandleable $next;


    /**
     * Constructor
     * @param class-string<RouteMiddleware> $middlewareClassName
     * @param RouteHandleable $next
     */
    public function __construct(string $middlewareClassName, RouteHandleable $next)
    {
        $this->middlewareClassName = $middlewareClassName;
        $this->next = $next;
    }


    /**
     * @inheritDoc
     */
    public function route(Request $request) : mixed
    {
        $instance = ($this->middlewareClassName)::create();
        return $instance->handle($request, $this->next);
    }
}