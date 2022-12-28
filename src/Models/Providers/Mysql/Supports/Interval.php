<?php

/** @noinspection DuplicatedCode */

namespace Magpie\Models\Providers\Mysql\Supports;

use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * A temporal interval specification
 */
final class Interval implements QueryArgumentable
{
    /**
     * @var int The interval value
     */
    public readonly int $value;
    /**
     * @var IntervalUnit The interval unit
     */
    public readonly IntervalUnit $unit;
    /**
     * @var array<Interval> Sub-intervals
     */
    public array $subIntervals = [];


    /**
     * Constructor
     * @param int $value
     * @param IntervalUnit $unit
     */
    public function __construct(int $value, IntervalUnit $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }


    /**
     * If there is sub-intervals
     * @return bool
     */
    public function hasSubIntervals() : bool
    {
        return count($this->subIntervals) > 0;
    }


    /**
     * Specify interval in microseconds
     * @param int $value
     * @return static
     */
    public static function inMicroseconds(int $value) : static
    {
        return new static($value, IntervalUnit::MICROSECOND);
    }


    /**
     * Specify interval in seconds
     * @param int $value
     * @return static
     */
    public static function inSeconds(int $value) : static
    {
        return new static($value, IntervalUnit::SECOND);
    }


    /**
     * Specify interval in minutes
     * @param int $value
     * @return static
     */
    public static function inMinutes(int $value) : static
    {
        return new static($value, IntervalUnit::MINUTE);
    }


    /**
     * Specify interval in hours
     * @param int $value
     * @return static
     */
    public static function inHours(int $value) : static
    {
        return new static($value, IntervalUnit::HOUR);
    }


    /**
     * Specify interval in days
     * @param int $value
     * @return static
     */
    public static function inDays(int $value) : static
    {
        return new static($value, IntervalUnit::DAY);
    }


    /**
     * Specify interval in weeks
     * @param int $value
     * @return static
     */
    public static function inWeeks(int $value) : static
    {
        return new static($value, IntervalUnit::WEEK);
    }


    /**
     * Specify interval in months
     * @param int $value
     * @return static
     */
    public static function inMonths(int $value) : static
    {
        return new static($value, IntervalUnit::MONTH);
    }


    /**
     * Specify interval in quarters
     * @param int $value
     * @return static
     */
    public static function inQuarters(int $value) : static
    {
        return new static($value, IntervalUnit::QUARTER);
    }


    /**
     * Specify interval in years
     * @param int $value
     * @return static
     */
    public static function inYears(int $value) : static
    {
        return new static($value, IntervalUnit::YEAR);
    }


    /**
     * @inheritDoc
     * @internal
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        if ($this->hasSubIntervals()) {
            return match ($this->unit) {
                IntervalUnit::SECOND => $this->finalizeSecondWithSubIntervals($context),
                IntervalUnit::MINUTE => $this->finalizeMinuteWithSubIntervals($context),
                IntervalUnit::HOUR => $this->finalizeHourWithSubIntervals($context),
                IntervalUnit::DAY => $this->finalizeDayWithSubIntervals($context),
                IntervalUnit::YEAR => $this->finalizeYearWithSubIntervals($context),
                default => throw new UnsupportedValueException($this->unit, _l('main unit with sub-intervals')),
            };
        }

        return static::finalizeStatement($this->value, $this->unit);
    }


    /**
     * Finalize a statement
     * @param string|int $value
     * @param IntervalUnit $unit
     * @param IntervalUnit|null $subUnit
     * @return QueryStatement
     */
    private static function finalizeStatement(string|int $value, IntervalUnit $unit, ?IntervalUnit $subUnit = null) : QueryStatement
    {
        $unitName = strtoupper($subUnit !== null ? $unit->value . '_' . $subUnit->value : $unit->value);
        return new QueryStatement('INTERVAL ? ' . $unitName, [$value]);
    }


    /**
     * Finalize the current interval with sub intervals and second as main unit
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeSecondWithSubIntervals(QueryContext $context) : QueryStatement
    {
        _used($context);

        // Supported: SECOND_MICROSECOND
        $microSecondInterval = null;

        foreach ($this->subIntervals as $subInterval) {
            if ($subInterval->hasSubIntervals()) throw new UnsupportedValueException($subInterval, _l('sub-interval'));
            switch ($subInterval->unit) {
                case IntervalUnit::MICROSECOND:
                    if ($microSecondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $microSecondInterval = $subInterval;
                    break;
                default:
                    throw new UnsupportedValueException($subInterval->unit, _l('sub-interval unit'));
            }
        }

        $minUnit = $microSecondInterval !== null ? IntervalUnit::MICROSECOND : throw new UnexpectedException();

        $outValue = $this->value . '.' . static::formatMicrosecond($microSecondInterval);

        return static::finalizeStatement($outValue, $this->unit, $minUnit);
    }


    /**
     * Finalize the current interval with sub intervals and minute as main unit
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeMinuteWithSubIntervals(QueryContext $context) : QueryStatement
    {
        _used($context);

        // Supported: MINUTE_MICROSECOND, MINUTE_SECOND
        $microSecondInterval = null;
        $secondInterval = null;

        foreach ($this->subIntervals as $subInterval) {
            if ($subInterval->hasSubIntervals()) throw new UnsupportedValueException($subInterval, _l('sub-interval'));
            switch ($subInterval->unit) {
                case IntervalUnit::MICROSECOND:
                    if ($microSecondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $microSecondInterval = $subInterval;
                    break;
                case IntervalUnit::SECOND:
                    if ($secondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $secondInterval = $subInterval;
                    break;
                default:
                    throw new UnsupportedValueException($subInterval->unit, _l('sub-interval unit'));
            }
        }

        $minUnit = $microSecondInterval !== null ? IntervalUnit::MICROSECOND :
            ($secondInterval !== null ? IntervalUnit::SECOND :
                throw new UnexpectedException()
            );

        $outValue = match ($minUnit) {
            IntervalUnit::SECOND => $this->value . ':' . static::formatSecond($secondInterval),
            IntervalUnit::MICROSECOND => $this->value . ':' . static::formatSecond($secondInterval) . '.' . static::formatMicrosecond($microSecondInterval),
            default => throw new UnexpectedException(),
        };

        return static::finalizeStatement($outValue, $this->unit, $minUnit);
    }


    /**
     * Finalize the current interval with sub intervals and hour as main unit
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeHourWithSubIntervals(QueryContext $context) : QueryStatement
    {
        _used($context);

        // Supported: HOUR_MICROSECOND, HOUR_SECOND, HOUR_MINUTE
        $microSecondInterval = null;
        $secondInterval = null;
        $minuteInterval = null;

        foreach ($this->subIntervals as $subInterval) {
            if ($subInterval->hasSubIntervals()) throw new UnsupportedValueException($subInterval, _l('sub-interval'));
            switch ($subInterval->unit) {
                case IntervalUnit::MICROSECOND:
                    if ($microSecondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $microSecondInterval = $subInterval;
                    break;
                case IntervalUnit::SECOND:
                    if ($secondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $secondInterval = $subInterval;
                    break;
                case IntervalUnit::MINUTE:
                    if ($minuteInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $minuteInterval = $subInterval;
                    break;
                default:
                    throw new UnsupportedValueException($subInterval->unit, _l('sub-interval unit'));
            }
        }

        $minUnit = $microSecondInterval !== null ? IntervalUnit::MICROSECOND :
            ($secondInterval !== null ? IntervalUnit::SECOND :
                ($minuteInterval !== null ? IntervalUnit::MINUTE :
                    throw new UnexpectedException()
                )
            );

        $outValue = match ($minUnit) {
            IntervalUnit::MINUTE => $this->value . ':' . static::formatMinute($minuteInterval),
            IntervalUnit::SECOND => $this->value . ':' . static::formatMinute($minuteInterval) . ':' . static::formatSecond($secondInterval),
            IntervalUnit::MICROSECOND => $this->value . ':' . static::formatMinute($minuteInterval) . ':' . static::formatSecond($secondInterval) . '.' . static::formatMicrosecond($microSecondInterval),
            default => throw new UnexpectedException(),
        };

        return static::finalizeStatement($outValue, $this->unit, $minUnit);
    }


    /**
     * Finalize the current interval with sub intervals and day as main unit
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeDayWithSubIntervals(QueryContext $context) : QueryStatement
    {
        _used($context);

        // Supported: DAY_MICROSECOND, DAY_SECOND, DAY_MINUTE, DAY_HOUR
        $microSecondInterval = null;
        $secondInterval = null;
        $minuteInterval = null;
        $hourInterval = null;

        foreach ($this->subIntervals as $subInterval) {
            if ($subInterval->hasSubIntervals()) throw new UnsupportedValueException($subInterval, _l('sub-interval'));
            switch ($subInterval->unit) {
                case IntervalUnit::MICROSECOND:
                    if ($microSecondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $microSecondInterval = $subInterval;
                    break;
                case IntervalUnit::SECOND:
                    if ($secondInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $secondInterval = $subInterval;
                    break;
                case IntervalUnit::MINUTE:
                    if ($minuteInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $minuteInterval = $subInterval;
                    break;
                case IntervalUnit::HOUR:
                    if ($hourInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $hourInterval = $subInterval;
                    break;
                default:
                    throw new UnsupportedValueException($subInterval->unit, _l('sub-interval unit'));
            }
        }

        $minUnit = $microSecondInterval !== null ? IntervalUnit::MICROSECOND :
            ($secondInterval !== null ? IntervalUnit::SECOND :
                ($minuteInterval !== null ? IntervalUnit::MINUTE :
                    ($hourInterval !== null ? IntervalUnit::HOUR :
                        throw new UnexpectedException()
                    )
                )
            );

        $outValue = match ($minUnit) {
            IntervalUnit::HOUR => $this->value . ' ' . static::formatHour($hourInterval),
            IntervalUnit::MINUTE => $this->value . ' ' . static::formatHour($hourInterval) . ':' . static::formatMinute($minuteInterval),
            IntervalUnit::SECOND => $this->value . ' ' . static::formatHour($hourInterval) . ':' . static::formatMinute($minuteInterval) . ':' . static::formatSecond($secondInterval),
            IntervalUnit::MICROSECOND => $this->value . ' ' . static::formatHour($hourInterval) . ':' . static::formatMinute($minuteInterval) . ':' . static::formatSecond($secondInterval) . '.' . static::formatMicrosecond($microSecondInterval),
            default => throw new UnexpectedException(),
        };

        return static::finalizeStatement($outValue, $this->unit, $minUnit);
    }


    /**
     * Finalize the current interval with sub intervals and year as main unit
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeYearWithSubIntervals(QueryContext $context) : QueryStatement
    {
        _used($context);

        // Supported: YEAR_MONTH
        $monthInterval = null;

        foreach ($this->subIntervals as $subInterval) {
            if ($subInterval->hasSubIntervals()) throw new UnsupportedValueException($subInterval, _l('sub-interval'));
            switch ($subInterval->unit) {
                case IntervalUnit::MONTH:
                    if ($monthInterval !== null) throw new DuplicatedKeyException($subInterval->unit->value);
                    $monthInterval = $subInterval;
                    break;
                default:
                    throw new UnsupportedValueException($subInterval->unit, _l('sub-interval unit'));
            }
        }

        $minUnit = $monthInterval !== null ? IntervalUnit::MONTH : throw new UnexpectedException();

        $outValue = $this->value . '-' . static::formatMonth($monthInterval);

        return static::finalizeStatement($outValue, $this->unit, $minUnit);
    }


    /**
     * Format a month interval
     * @param Interval|null $subInterval
     * @return int
     * @throws UnsupportedException
     */
    private static function formatMonth(?Interval $subInterval) : int
    {
        if ($subInterval === null) return 0;

        if ($subInterval->value < 0 || $subInterval->value >= 12) throw new UnsupportedValueException($subInterval->value, IntervalUnit::MONTH->value);

        return $subInterval->value;
    }


    /**
     * Format an hour interval
     * @param Interval|null $subInterval
     * @return int
     * @throws UnsupportedException
     */
    private static function formatHour(?Interval $subInterval) : int
    {
        if ($subInterval === null) return 0;

        if ($subInterval->value < 0 || $subInterval->value >= 24) throw new UnsupportedValueException($subInterval->value, IntervalUnit::HOUR->value);

        return $subInterval->value;
    }


    /**
     * Format a minute interval
     * @param Interval|null $subInterval
     * @return int
     * @throws UnsupportedException
     */
    private static function formatMinute(?Interval $subInterval) : int
    {
        if ($subInterval === null) return 0;

        if ($subInterval->value < 0 || $subInterval->value >= 60) throw new UnsupportedValueException($subInterval->value, IntervalUnit::MINUTE->value);

        return $subInterval->value;
    }


    /**
     * Format a second interval
     * @param Interval|null $subInterval
     * @return int
     * @throws UnsupportedException
     */
    private static function formatSecond(?Interval $subInterval) : int
    {
        if ($subInterval === null) return 0;

        if ($subInterval->value < 0 || $subInterval->value >= 60) throw new UnsupportedValueException($subInterval->value, IntervalUnit::SECOND->value);

        return $subInterval->value;
    }


    /**
     * Format a microsecond interval
     * @param Interval|null $subInterval
     * @return string
     * @throws UnsupportedException
     */
    private static function formatMicrosecond(?Interval $subInterval) : string
    {
        if ($subInterval === null) return 0;

        if ($subInterval->value < 0 || $subInterval->value >= 1000000) throw new UnsupportedValueException($subInterval->value, IntervalUnit::MICROSECOND->value);

        $ret = '' . $subInterval->value;
        while (strlen($ret) < 6) $ret = '0' . $ret;

        return $ret;
    }
}