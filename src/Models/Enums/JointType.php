<?php

namespace Magpie\Models\Enums;

/**
 * Join-query joint type
 */
enum JointType : string
{
    /**
     * Inner join
     */
    case INNER = 'inner';
    /**
     * Left join
     */
    case LEFT = 'left';
    /**
     * Right join
     */
    case RIGHT = 'right';
}