<?php

namespace Magpie\Routes\Constants;

use Magpie\General\Traits\StaticClass;

/**
 * Common priority constants for RouteMiddleware
 */
class RouteMiddlewarePriority
{
    use StaticClass;

    /**
     * Top priority
     */
    public const TOP = 0;
    /**
     * Urgent
     */
    public const URGENT = 10;
    /**
     * High priority
     */
    public const HIGH = 50;
    /**
     * Normal priority
     */
    public const NORMAL = 1000;
    /**
     * Default priority
     */
    public const DEFAULT = 10000;
    /**
     * Lowest possible priority
     */
    public const LOWEST = 99999;
}