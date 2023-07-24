<?php

namespace Magpie\Routes\Impls;

use Magpie\General\Sugars\Quote;

/**
 * Flatten route information
 * @internal
 */
class RouteInfo
{
    /**
     * Indication of a variable
     */
    public const VARIABLE = '{?}';

    /**
     * @var string|null Domain pattern
     */
    public readonly ?string $domain;
    /**
     * @var string Path pattern
     */
    public readonly string $path;
    /**
     * @var string HTTP method
     */
    public readonly string $method;
    /**
     * @var RouteLanding Landing target
     */
    public readonly RouteLanding $target;


    /**
     * Constructor
     * @param string|null $domain
     * @param string $path
     * @param string $method
     * @param RouteLanding $target
     */
    public function __construct(?string $domain, string $path, string $method, RouteLanding $target)
    {
        $this->domain = $domain;
        $this->path = static::formatActualPath($path, $target);
        $this->method = $method;
        $this->target = $target;
    }


    /**
     * Format for actual path
     * @param string $path
     * @param RouteLanding $target
     * @return string
     */
    protected static function formatActualPath(string $path, RouteLanding $target) : string
    {
        $vLength = strlen(static::VARIABLE);

        foreach ($target->argumentNames as $argumentName) {
            $vPos = strpos($path, static::VARIABLE);
            if ($vPos === false) break;

            $path = substr($path, 0, $vPos) . Quote::brace($argumentName) . substr($path, $vPos + $vLength);
        }

        return $path;
    }
}