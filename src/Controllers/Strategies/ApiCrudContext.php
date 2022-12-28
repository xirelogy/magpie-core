<?php

namespace Magpie\Controllers\Strategies;

/**
 * Context for CRUD operations
 */
abstract class ApiCrudContext
{
    /**
     * @var ApiCrudPurpose The CRUD purpose
     */
    public readonly ApiCrudPurpose $purpose;


    /**
     * Constructor
     * @param ApiCrudPurpose $purpose
     */
    public function __construct(ApiCrudPurpose $purpose)
    {
        $this->purpose = $purpose;
    }
}