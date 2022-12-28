<?php

namespace Magpie\Routes\Handlers;

use Closure;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;

/**
 * Implementation of RouteHandleable using closure
 */
class ClosureRouteHandler implements RouteHandleable
{
    /**
     * @var Closure Redirecting closure
     */
    protected Closure $fn;


    /**
     * Constructor
     * @param callable(Request):mixed $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function route(Request $request) : mixed
    {
        return ($this->fn)($request);
    }


    /**
     * Create instance
     * @param callable(Request):mixed $fn
     * @return static
     */
    public static function for(callable $fn) : static
    {
        return new static($fn);
    }
}