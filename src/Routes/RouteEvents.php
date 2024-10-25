<?php

namespace Magpie\Routes;

use Magpie\General\Contexts\ClosureReleaseScoped;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Traits\StaticClass;
use Magpie\Routes\Concepts\RouteHandledEventable;
use Magpie\Routes\Concepts\RouteHookEventable;
use Magpie\Routes\Impls\RouteEventHost;

/**
 * May subscribe to routing events
 */
final class RouteEvents
{
    use StaticClass;


    /**
     * Subscribe to route handled
     * @param RouteHandledEventable $eventHandle
     * @return Scoped
     */
    public static function subscribeRouteHandled(RouteHandledEventable $eventHandle) : Scoped
    {
        $key = RouteEventHost::instance()->subscribeRouteHandled($eventHandle);

        return ClosureReleaseScoped::create(fn() => RouteEventHost::instance()->unsubscribe($key));
    }


    /**
     * Subscribe to route hook (before/after)
     * @param RouteHookEventable $eventHandle
     * @return Scoped
     */
    public static function subscribeRouteHook(RouteHookEventable $eventHandle) : Scoped
    {
        $key = RouteEventHost::instance()->subscribeRouteHook($eventHandle);

        return ClosureReleaseScoped::create(fn() => RouteEventHost::instance()->unsubscribe($key));
    }


    /**
     * Subscribe to controller-method route hook (before/after)
     * @param RouteHookEventable $eventHandle
     * @return Scoped
     */
    public static function subscribeControllerMethodHook(RouteHookEventable $eventHandle) : Scoped
    {
        $key = RouteEventHost::instance()->subscribeControllerMethodHook($eventHandle);

        return ClosureReleaseScoped::create(fn () => RouteEventHost::instance()->unsubscribe($key));
    }
}