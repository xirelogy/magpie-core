<?php

namespace Magpie\Routes\Annotations;

use Attribute;
use Magpie\HttpServer\CommonMethod;

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
     * @var string|CommonMethod|array<string|CommonMethod>|null Supported method(s)
     */
    public readonly string|CommonMethod|array|null $method;


    /**
     * Constructor
     * @param string $path Entry path
     * @param string|CommonMethod|array|null $method Supported method(s)
     */
    public function __construct(string $path, string|CommonMethod|array|null $method = null)
    {
        $this->path = $path;
        $this->method = $method;
    }
}