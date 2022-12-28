<?php

namespace Magpie\Models\Enums;

/**
 * Relationship with previous condition
 */
enum WhereJoinType : string
{
    /**
     * 'and' relationship
     */
    case AND = 'and';
    /**
     * 'or' relationship
     */
    case OR = 'or';
}