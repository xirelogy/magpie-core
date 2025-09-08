<?php

namespace Magpie\Controllers\Constants;

use Magpie\General\Traits\StaticClass;

/**
 * Names of the CRUD operation's method
 */
final class CommonApiCrudMethodName
{
    use StaticClass;

    /**
     * C - Create
     */
    public const CREATE = 'postRoot';
    /**
     * L - List, a special type of 'read'
     */
    public const LIST = 'getRoot';
    /**
     * R - read
     */
    public const READ = 'getItem';
    /**
     * U - update
     */
    public const UPDATE = 'putItem';
    /**
     * D - delete
     */
    public const DELETE = 'deleteItem';
}