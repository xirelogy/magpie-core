<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;

/**
 * Declares that current constant should be included as string of 'encoded name'
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class NamedString
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }
}
