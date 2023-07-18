<?php

namespace Magpie\Commands\Attributes;

use Attribute;

/**
 * Declare description of command argument
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CommandArgumentDescription
{
    /**
     * @var string Argument name
     */
    public string $name;
    /**
     * @var string Description of the command argument
     */
    public string $desc;


    /**
     * Constructor
     * @param string $name
     * @param string $desc
     */
    public function __construct(string $name, string $desc)
    {
        $this->name = $name;
        $this->desc = $desc;
    }
}