<?php

namespace Magpie\Routes\Concepts;

use Magpie\HttpServer\Request;

/**
 * May handle event of a route handler being determined
 */
interface RouteHandledEventable
{
    /**
     * Get notified of a route handler determined
     * @param Request $request
     * @param RouteHandleable $handler
     * @return void
     */
    public function notifyRouteHandled(Request $request, RouteHandleable $handler) : void;
}