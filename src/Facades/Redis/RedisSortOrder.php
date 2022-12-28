<?php

namespace Magpie\Facades\Redis;

/**
 * Sorting order
 */
enum RedisSortOrder : string
{
    /**
     * Ascending
     */
    case ASC = 'asc';
    /**
     * Descending
     */
    case DESC = 'desc';
}