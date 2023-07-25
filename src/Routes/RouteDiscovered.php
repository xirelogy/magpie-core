<?php

namespace Magpie\Routes;

use Magpie\General\Sugars\Quote;
use Magpie\Objects\Uri;
use Stringable;

/**
 * A discovered route
 */
abstract class RouteDiscovered implements Stringable
{
    /**
     * @var string Associated host
     */
    protected string $host;
    /**
     * @var string Discovered path
     */
    protected string $path;


    /**
     * Constructor
     * @param string $host
     * @param string $url
     */
    protected function __construct(string $host, string $url)
    {
        $this->host = $host;
        $this->path = $url;
    }


    /**
     * Specify the host
     * @param string $host
     * @return $this
     */
    public function withHost(string $host) : static
    {
        $this->host = $host;
        return $this;
    }


    /**
     * Check if candidate host is matched
     * @param string $candidateHost
     * @return bool
     */
    public function isMatchHost(string $candidateHost) : bool
    {
        return $this->host === $candidateHost;
    }


    /**
     * Replace domain/route argument with given value
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setArgument(string $name, string $value) : static
    {
        return $this
            ->setDomainArgument($name, $value)
            ->setRouteArgument($name, $value)
            ;
    }


    /**
     * Replace domain argument with given value
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setDomainArgument(string $name, string $value) : static
    {
        $this->host = str_replace(Quote::brace($name), $value, $this->host);

        return $this;
    }


    /**
     * Replace route argument with given value
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setRouteArgument(string $name, string $value) : static
    {
        $this->path = str_replace(Quote::brace($name), $value, $this->path);

        return $this;
    }


    /**
     * Match given path with current route, capturing path arguments at the same time
     * @param string $path Path to be matched
     * @return array<string, string>|null Path arguments matched, or null if path is not matched
     */
    public function matchPath(string $path) : ?array
    {
        $thisNodes = static::explodeNodes($this->path);
        $pathNodes = static::explodeNodes($path);

        $thisNodesCount = count($thisNodes);
        if ($thisNodesCount <= 0) return null;

        $pathNodesCount = count($pathNodes);
        if ($thisNodesCount != $pathNodesCount) return null;

        $ret = [];
        for ($i = 0; $i < $thisNodesCount; ++$i) {
            $thisNode = $thisNodes[$i];
            $pathNode = $pathNodes[$i];

            $routeArgument = static::decodeRouteArgument($thisNode);
            if ($routeArgument !== null) {
                $ret[$routeArgument] = $pathNode;
                continue;
            }

            if ($thisNode != $pathNode) return null;
        }

        return $ret;
    }


    /**
     * The relative URL
     * @return Uri
     */
    public function relative() : Uri
    {
        return Uri::safeParse($this->path);
    }


    /**
     * The absolute URL
     * @param string|null $schema
     * @return Uri
     */
    public function absolute(?string $schema = null) : Uri
    {
        $path = $this->path;
        if (!str_starts_with($path, '/')) $path = "/$path";

        if (is_empty_string($this->host)) {
            $ret = $path;
        } else {
            $ret = '//' . $this->host . $path;
            if ($schema !== null) $ret = $schema . ':' . $ret;
        }

        return Uri::safeParse($ret);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->relative();
    }


    /**
     * Explode path into nodes
     * @param string $path
     * @return array<string>
     */
    protected static function explodeNodes(string $path) : array
    {
        $exploded = explode('/', $path);

        while (end($exploded) === '') {
            array_pop($exploded);
        }

        return $exploded;
    }


    /**
     * Check and decode the given route node as a route argument
     * @param string $routeNode
     * @return string|null
     */
    protected static function decodeRouteArgument(string $routeNode) : ?string
    {
        if (!str_starts_with($routeNode, '{') || !str_ends_with($routeNode, '}')) return null;
        return substr($routeNode, 1, -1);
    }
}