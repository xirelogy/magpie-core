<?php

namespace Magpie\Routes;

use Exception;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\General\Traits\StaticClass;
use Magpie\Routes\Impls\RegExRouteDomainMatch;
use Magpie\Routes\Impls\RouteDomainMatch;
use Magpie\Routes\Impls\VariableRouteDomainMatch;
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
     * @var array<string, string> Domain map for exact domains
     */
    protected static array $exactDomains = [];
    /**
     * @var array<RouteDomainMatch> Domain matched by rules
     */
    protected static array $matchedDomains = [];


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
        static::includeHostname($domainKey, $domainKey);

        foreach ($domain->altDomains as $altDomain) {
            static::includeHostname($altDomain, $domainKey);
        }
    }


    /**
     * Include a hostname to be routed to given target
     * @param string $hostname
     * @param string $targetDomainKey
     * @return void
     * @throws Exception
     */
    private static function includeHostname(string $hostname, string $targetDomainKey) : void
    {
        if ($hostname === '') return;

        if (str_contains($hostname, '*')) {
            static::$matchedDomains[] = RegExRouteDomainMatch::create($hostname, $targetDomainKey);
            return;
        }

        if (str_contains($hostname, '{')) {
            static::$matchedDomains[] = VariableRouteDomainMatch::create($hostname, $targetDomainKey);
            return;
        }

        if (array_key_exists($hostname, static::$exactDomains)) throw new DuplicatedKeyException($hostname, _l('route domain'));
        static::$exactDomains[$hostname] = $targetDomainKey;
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
     * @param array|null $domainArguments
     * @return RouteDomain|null
     * @internal
     */
    public static function _route(string $hostname, ?array &$domainArguments = null) : ?RouteDomain
    {
        // Match for exact match
        if (array_key_exists($hostname, static::$exactDomains)) {
            $matchedDomainKey = static::$exactDomains[$hostname];
            return static::$domains[$matchedDomainKey] ?? null;
        }

        // Match using matching rules
        foreach (static::$matchedDomains as $matchedDomain) {
            $domainArguments = [];
            if (!$matchedDomain->isMatched($hostname, $domainArguments)) continue;

            return static::$domains[$matchedDomain->targetDomainKey] ?? null;
        }

        // Fallback to wildcard
        if (array_key_exists('', static::$domains)) {
            return static::$domains[''];
        }

        return null;
    }
}