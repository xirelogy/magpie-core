<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Specify the variable where condition to be fulfilled for route to be effective
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RouteIf
{
    /**
     * @var string Name of the variable to be depends on for this route
     */
    public readonly string $name;


    /**
     * Constructor
     * @param string $name Name of the variable to be depends on for this route
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}