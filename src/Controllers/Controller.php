<?php

namespace Magpie\Controllers;

use Closure;
use Exception;
use Magpie\Controllers\Concepts\ControllerCallable;
use Magpie\Exceptions\MethodNotFoundException;
use Magpie\HttpServer\Request;

/**
 * A controller to serve web request
 */
abstract class Controller
{
    /**
     * Call and route from given method
     * @param string $methodName
     * @param Request $request
     * @return mixed
     * @throws Exception
     * @internal
     */
    protected final function _call(string $methodName, Request $request) : mixed
    {
        // Check method name and construct the closure
        if (!method_exists($this, $methodName)) throw new MethodNotFoundException($this, $methodName);

        $fn = function(Request $request, array $routeArguments) use($methodName) : mixed {
            return $this->{$methodName}($request, ...$routeArguments);
        };

        // Create callable
        $callable = new class($fn) implements ControllerCallable {
            /**
             * Constructor
             * @param Closure $fn
             */
            public function __construct(
                protected Closure $fn,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function call(Request $request, array $routeArguments) : mixed
            {
                return ($this->fn)($request, $routeArguments);
            }
        };

        // Relay to callable
        $routeArguments = iter_flatten($request->routeArguments->all(), false);
        return $this->onCall($callable, $request, $routeArguments);
    }


    /**
     * Handle a route call
     * @param ControllerCallable $callable
     * @param Request $request
     * @param array $routeArguments
     * @return mixed
     * @throws Exception
     */
    protected function onCall(ControllerCallable $callable, Request $request, array $routeArguments) : mixed
    {
        return $callable->call($request, $routeArguments);
    }


    /**
     * Route a request
     * @param string $methodName
     * @param Request $request
     * @return mixed
     * @throws Exception
     * @internal
     */
    public static final function _route(string $methodName, Request $request) : mixed
    {
        $instance = static::createInstance($methodName, $request);

        return $instance->_call($methodName, $request);
    }


    /**
     * Create an instance for routing
     * @param string $methodName
     * @param Request $request
     * @return static
     */
    protected static function createInstance(string $methodName, Request $request) : static
    {
        _used($methodName, $request);

        return new static();
    }
}