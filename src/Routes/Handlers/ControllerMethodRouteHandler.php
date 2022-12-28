<?php

namespace Magpie\Routes\Handlers;

use Magpie\Controllers\Controller;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;

/**
 * Handle routing using a specific method in a controller
 */
class ControllerMethodRouteHandler implements RouteHandleable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'controller-method';

    /**
     * @var string Controller's class name
     */
    public readonly string $controllerClassName;
    /**
     * @var string Method's name
     */
    public readonly string $methodName;


    /**
     * Constructor
     * @param string $controllerClassName
     * @param string $methodName
     */
    public function __construct(string $controllerClassName, string $methodName)
    {
        $this->controllerClassName = $controllerClassName;
        $this->methodName = $methodName;
    }


    /**
     * @inheritDoc
     */
    public function route(Request $request) : mixed
    {
        $controllerClassName = $this->controllerClassName;
        if (!is_subclass_of($controllerClassName, Controller::class)) throw new ClassNotOfTypeException($controllerClassName, Controller::class);
        return $controllerClassName::_route($this->methodName, $request);
    }
}