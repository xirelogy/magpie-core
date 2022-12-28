<?php

namespace Magpie\Models\Impls;

/**
 * Mode to apply filter while preparing the 'SELECT' query statement
 */
enum FilterApplyMode : int
{
    /**
     * Filter must not be applied
     */
    case NO = 0;
    /**
     * Filter is to be applied
     */
    case YES = 1;
    /**
     * A specific filter to limit to first result should be applied if possible
     */
    case FIRST = 2;
}