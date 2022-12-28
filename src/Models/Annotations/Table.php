<?php

namespace Magpie\Models\Annotations;

use Attribute;

/**
 * Define database table
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * @var string Table class name
     */
    public string $name;


    /**
     * Constructor
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}