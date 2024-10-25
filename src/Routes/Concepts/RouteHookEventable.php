<?php

namespace Magpie\Routes\Concepts;

use Magpie\HttpServer\Request;

/**
 * May hook (before/after) a route
 */
interface RouteHookEventable
{
    /**
     * Get notified of a route will be starting
     * @param RouteHandleable $handler
     * @param Request $request
     * @return void
     */
    public function notifyBeforeRoute(RouteHandleable $handler, Request $request) : void;


    /**
     * Get notified of a route has response
     * @param RouteHandleable $handler
     * @param mixed $response
     * @return void
     */
    public function notifyAfterRoute(RouteHandleable $handler, mixed $response) : void;
}