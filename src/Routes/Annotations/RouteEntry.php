<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Route entry
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RouteEntry
{
    /**
     * @var string Entry path
     */
    public readonly string $path;
    /**
     * @var string|array<string>|null Supported method(s)
     */
    public readonly string|array|null $method;


    /**
     * Constructor
     * @param string $path Entry path
     * @param string|array<string>|null $method Supported method(s)
     */
    public function __construct(string $path, string|array|null $method = null)
    {
        $this->path = $path;
        $this->method = $method;
    }
}