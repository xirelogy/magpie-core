<?php

namespace Magpie\Commands\Attributes;

use Attribute;

/**
 * Declare the description of a command (localizable)
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CommandDescriptionL
{
    /**
     * @var string Description of the command (localizable)
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