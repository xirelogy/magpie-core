<?php

namespace Magpie\Controllers\Strategies;

/**
 * Specific purpose of an API-based CRUD request
 */
enum ApiSpecificCrudPurpose : string
{
    /**
     * Read: list request
     */
    case LIST = 'list';
    /**
     * Read: get request
     */
    case GET = 'get';
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