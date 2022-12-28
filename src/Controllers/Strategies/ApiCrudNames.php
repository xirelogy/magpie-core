<?php

namespace Magpie\Controllers\Strategies;

/**
 * API CRUD operation names
 */
class ApiCrudNames
{
    /**
     * @var string Object name for singular
     */
    public readonly string $singularName;
    /**
     * @var string Objects name for plural
     */
    public readonly string $pluralName;


    /**
     * Constructor
     * @param string $singularName
     * @param string $pluralName
     */
    public function __construct(string $singularName, string $pluralName)
    {
        $this->singularName = $singularName;
        $this->pluralName = $pluralName;
    }
}