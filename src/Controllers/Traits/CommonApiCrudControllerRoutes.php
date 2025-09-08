<?php

/** @noinspection PhpUnused */

namespace Magpie\Controllers\Traits;

use Magpie\Controllers\Constants\CommonApiCrudMethodName;
use Magpie\Routes\Concepts\RouteDiscoverable;
use Magpie\Routes\RouteDiscovered;

/**
 * Common CRUD functionalities can be discovered using route functions
 */
trait CommonApiCrudControllerRoutes
{
    /**
     * Get the route for CREATE (C) operation
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public static function routeOfCreate(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf(static::class, CommonApiCrudMethodName::CREATE);
    }


    /**
     * Get the route for LIST (L) operation
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public static function routeOfList(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf(static::class, CommonApiCrudMethodName::LIST);
    }


    /**
     * Get the route for READ (R) operation
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public static function routeOfRead(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf(static::class, CommonApiCrudMethodName::READ);
    }


    /**
     * Get the route for UPDATE (U) operation
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public static function routeOfUpdate(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf(static::class, CommonApiCrudMethodName::UPDATE);
    }


    /**
     * Get the route for DELETE (D) operation
     * @param RouteDiscoverable $source
     * @return RouteDiscovered|null
     */
    public static function routeOfDelete(RouteDiscoverable $source) : ?RouteDiscovered
    {
        return $source->routeOf(static::class, CommonApiCrudMethodName::DELETE);
    }
}