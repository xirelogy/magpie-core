<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Mark the method to be invoked to produce route variable
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RouteVariable
{
    /**
     * @var string Name of the variable
     */
    public readonly string $name;


    /**
     * Constructor
     * @param string $name Name of the variable
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}