<?php

namespace Magpie\Routes\Constants;

use Magpie\General\Traits\StaticClass;

/**
 * Priority for given purpose of RouteMiddleware
 */
class RouteMiddlewarePurposePriority
{
    use StaticClass;

    /**
     * CORS validation and setup
     */
    public const CORS = RouteMiddlewarePriority::TOP + 2;
    /**
     * Throttle check
     */
    public const THROTTLE = RouteMiddlewarePriority::URGENT + 1;
    /**
     * Authentication
     */
    public const AUTHENTICATE = RouteMiddlewarePriority::HIGH + 2;
    /**
     * Authorization
     */
    public const AUTHORIZE = RouteMiddlewarePriority::HIGH + 10;
}