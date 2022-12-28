<?php

namespace Magpie\Routes\Annotations;

use Attribute;
use Magpie\Routes\RouteMiddleware;

/**
 * Declare that a specific middleware should be used
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RouteUseMiddleware
{
    /**
     * @var class-string<RouteMiddleware> Class name for the middleware to be used
     */
    public readonly string $className;


    /**
     * Constructor
     * @param class-string<RouteMiddleware> $className Class name for the middleware to be used
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }
}