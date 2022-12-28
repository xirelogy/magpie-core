<?php

namespace Magpie\Routes;

use Exception;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\General\Traits\StaticClass;
use Magpie\System\HardCore\SourceCache;

/**
 * Route definition and discovery
 */
class RouteRegistry
{
    use StaticClass;


    /**
     * @var array<string, RouteDomain> Domain map (direct)
     */
    protected static array $domains = [];


    /**
     * Include a domain to be routed
     * @param RouteDomain $domain
     * @return void
     * @throws Exception
     */
    public static function includeDomain(RouteDomain $domain) : void
    {
        $domainKey = $domain->domain ?? '';
        if (array_key_exists($domainKey, static::$domains)) throw new DuplicatedKeyException($domainKey, _l('route domain'));

        static::$domains[$domainKey] = $domain;
    }


    /**
     * Save the routes to source cache
     * @return void
     * @throws Exception
     */
    public static function saveSourceCache() : void
    {
        foreach (static::$domains as $domain) {
            SourceCache::instance()->setCache($domain::class, $domain->_sourceCacheExport());
        }
    }


    /**
     * Delete routes from source cache
     * @return void
     */
    public static function deleteSourceCache() : void
    {
        foreach (static::$domains as $domain) {
            SourceCache::instance()->deleteCache($domain::class);
            $domain->_unboot();
        }
    }


    /**
     * Find a domain to be routed
     * @param string $hostname
     * @return RouteDomain|null
     * @internal
     */
    public static function _route(string $hostname) : ?RouteDomain
    {
        // Find exact match
        if (array_key_exists($hostname, static::$domains)) {
            return static::$domains[$hostname];
        }

        // Fallback to wildcard
        if (array_key_exists('', static::$domains)) {
            return static::$domains[''];
        }

        return null;
    }
}