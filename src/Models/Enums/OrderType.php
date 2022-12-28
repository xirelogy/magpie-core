<?php

namespace Magpie\Models\Enums;

/**
 * Query order type
 */
enum OrderType : string
{
    /**
     * Ascending ordering
     */
    case ASC = 'asc';
    /**
     * Descending ordering
     */
    case DESC = 'desc';
}