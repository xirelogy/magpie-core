<?php

namespace Magpie\Routes\Concepts;

use Magpie\Controllers\Controller;
use Magpie\HttpServer\Request;

/**
 * May hook (before/after) a controller-method route
 */
interface RouteControllerMethodHookEventable
{
    /**
     * Get notified of a controller method in route will be starting
     * @param class-string<Controller> $controllerClass
     * @param string $methodName
     * @param Request $request
     * @param array $routeArguments
     * @return void
     */
    public function notifyBeforeControllerMethod(string $controllerClass, string $methodName, Request $request, array $routeArguments) : void;


    /**
     * Get notified of a controller method in route has initial (raw) response
     * @param class-string<Controller> $controllerClass
     * @param string $methodName
     * @param mixed $response
     * @return void
     */
    public function notifyAfterControllerMethod(string $controllerClass, string $methodName, mixed $response) : void;

}