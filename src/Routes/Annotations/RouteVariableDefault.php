<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Set the default value for route variable
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RouteVariableDefault
{
    /**
     * @var string Name of the variable
     */
    public readonly string $name;
    /**
     * @var mixed Value to set
     */
    public readonly mixed $value;


    /**
     * Constructor
     * @param string $name Name of the variable
     * @param mixed $value Value to set as default
     */
    public function __construct(string $name, mixed $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
}