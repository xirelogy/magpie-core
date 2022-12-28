<?php

namespace Magpie\Models\Providers\Mysql\Supports;

use Magpie\General\Traits\StaticClass;
use Magpie\Models\ColumnExpression;
use Magpie\Models\ColumnName;

/**
 * Common function expressions specific for MySQL
 */
class CommonFunctions
{
    use StaticClass;


    /**
     * Add interval to given date
     * @param ColumnName $baseColumnName
     * @param Interval $interval
     * @param Interval ...$subIntervals
     * @return ColumnExpression
     */
    public static function dateAdd(ColumnName $baseColumnName, Interval $interval, Interval ...$subIntervals) : ColumnExpression
    {
        return ColumnExpression::function('date_add', $baseColumnName, static::mergeInterval($interval, ...$subIntervals));
    }


    /**
     * Subtract interval from given date
     * @param ColumnName $baseColumnName
     * @param Interval $interval
     * @param Interval ...$subIntervals
     * @return ColumnExpression
     */
    public static function dateSub(ColumnName $baseColumnName, Interval $interval, Interval ...$subIntervals) : ColumnExpression
    {
        return ColumnExpression::function('date_sub', $baseColumnName, static::mergeInterval($interval, ...$subIntervals));
    }


    /**
     * Merge sub-intervals into the main interval
     * @param Interval $interval
     * @param Interval ...$subIntervals
     * @return Interval
     */
    private static function mergeInterval(Interval $interval, Interval ...$subIntervals) : Interval
    {
        if ($interval->hasSubIntervals()) return $interval;
        if (count($subIntervals) <= 0) return $interval;

        $ret = new Interval($interval->value, $interval->unit);

        foreach ($subIntervals as $subInterval) {
            $ret->subIntervals[] = $subInterval;
        }

        return $ret;
    }
}