<?php

namespace Magpie\Controllers\Strategies;

/**
 * Purpose of an API-based CRUD request
 */
enum ApiCrudPurpose : string
{
    /**
     * General read request
     */
    case READ = 'read';
    /**
     * Write: creation request
     */
    case CREATE = 'create';
    /**
     * Write: edit request
     */
    case EDIT = 'edit';
    /**
     * Write: delete request
     */
    case DELETE = 'delete';
}