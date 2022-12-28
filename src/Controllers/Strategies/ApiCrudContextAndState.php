<?php

namespace Magpie\Controllers\Strategies;

/**
 * Context and state for CRUD operations
 */
class ApiCrudContextAndState
{
    /**
     * @var ApiCrudContext CRUD context
     */
    public readonly ApiCrudContext $context;
    /**
     * @var ApiCrudState CRUD state
     */
    public readonly ApiCrudState $crudState;


    /**
     * Constructor
     * @param ApiCrudContext $context
     * @param ApiCrudState $state
     */
    public function __construct(ApiCrudContext $context, ApiCrudState $state)
    {
        $this->context = $context;
        $this->crudState = $state;
    }
}