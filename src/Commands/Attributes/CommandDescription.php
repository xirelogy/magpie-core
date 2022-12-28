<?php

namespace Magpie\Commands\Attributes;

use Attribute;

/**
 * Declare the description of a command
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CommandDescription
{
    /**
     * @var string Description of the command
     */
    public string $desc;


    /**
     * Constructor
     * @param string $desc
     */
    public function __construct(string $desc)
    {
        $this->desc = $desc;
    }
}