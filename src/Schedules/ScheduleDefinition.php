<?php

namespace Magpie\Schedules;

use BackedEnum;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Schedules\Concepts\ScheduleDefinable;
use Magpie\Schedules\Concepts\ScheduleRunnable;
use Magpie\Schedules\Constants\ScheduleDayOfMonth;
use Magpie\Schedules\Constants\ScheduleDayOfWeek;
use Magpie\Schedules\Impls\ScheduledEntry;
use Magpie\Schedules\Impls\ScheduleRunner;

/**
 * Schedule definition
 */
abstract class ScheduleDefinition implements ScheduleDefinable
{
    /**
     * @var ScheduleRunnable Target to run for current definition
     */
    protected readonly ScheduleRunnable $runner;
    /**
     * @var string Cron expression
     */
    protected string $cronExpression = '* * * * *';
    /**
     * @var string|null Specific timezone to evaluate current definition
     */
    protected ?string $timezone = null;
    /**
     * @var bool If running in background
     */
    protected bool $isRunInBackground = false;


    /**
     * Constructor
     */
    protected function __construct(ScheduleRunnable $runner)
    {
        $this->runner = $runner;
    }


    /**
     * @inheritDoc
     */
    public final function everyMinute() : static
    {
        return $this->cronSplice([
            1 => '*',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyTwoMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/2',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyThreeMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/3',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyFourMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/4',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyFiveMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/5',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everySixMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/6',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyTenMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/10',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyTwelveMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/12',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyFifteenMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/15',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyTwentyMinutes() : static
    {
        return $this->cronSplice([
            1 => '*/20',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyThirtyMinutes() : static
    {
        return $this->cronSplice([
            1 => '0,30',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function hourlyAt(int ...$minutes) : static
    {
        return $this->cronSplice([
            1 => static::formatMinuteSpec($minutes),
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyHour() : static
    {
        return $this->cronSplice([
            2 => '*',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyTwoHours() : static
    {
        return $this->cronSplice([
            2 => '*/2',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyThreeHours() : static
    {
        return $this->cronSplice([
            2 => '*/3',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everyFourHours() : static
    {
        return $this->cronSplice([
            2 => '*/4',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function everySixHours() : static
    {
        return $this->cronSplice([
            2 => '*/6',
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function dailyAt(string $time) : static
    {
        [$hour, $minute] = static::getTimeComponents($time);

        return $this->cronSplice([
            1 => $minute,
            2 => $hour,
        ]);
    }


    /**
     * @inheritDoc
     */
    public final function dailyAtHours(int ...$hours) : static
    {
        if (count($hours) <= 0) throw new MissingArgumentException();

        return $this->cronSplice([
            2 => implode(',', $hours),
        ]);
    }


    /**
     * @inheritDoc
     */
    public function onWeekdays() : static
    {
        return $this->cronSplice([
            5 => static::formatCronRange(ScheduleDayOfWeek::MONDAY, ScheduleDayOfWeek::FRIDAY),
        ]);
    }



    /**
     * @inheritDoc
     */
    public function onWeekends() : static
    {
        return $this->onDayOfWeek(ScheduleDayOfWeek::SATURDAY, ScheduleDayOfWeek::SUNDAY);
    }


    /**
     * @inheritDoc
     */
    public final function onDayOfWeek(ScheduleDayOfWeek ...$days) : static
    {
        if (count($days) <= 0) throw new MissingArgumentException();

        return $this->cronSplice([
            5 => static::formatCronItems($days)
        ]);
    }


    /**
     * @inheritDoc
     */
    public function onDayOfMonth(int|ScheduleDayOfMonth ...$days) : static
    {
        if (count($days) <= 0) throw new MissingArgumentException();

        return $this->cronSplice([
            3 => static::formatCronItems($days)
        ]);
    }


    /**
     * @inheritDoc
     */
    public function onMonth(int ...$months) : static
    {
        if (count($months) <= 0) throw new MissingArgumentException();

        return $this->cronSplice([
            4 => static::formatCronItems($months)
        ]);
    }


    /**
     * Format minute specification
     * @param array<int> $minutes
     * @return string
     */
    protected static final function formatMinuteSpec(array $minutes) : string
    {
        if (count($minutes) > 0) {
            return static::formatCronItems($minutes);
        } else {
            return '0';
        }
    }


    /**
     * Get the representation of range
     * @param string|int|BackedEnum $startItem
     * @param string|int|BackedEnum $endItem
     * @return string
     */
    protected static final function formatCronRange(string|int|BackedEnum $startItem, string|int|BackedEnum $endItem) : string
    {
        return static::formatCronItem($startItem) . '-' . static::formatCronItem($endItem);
    }


    /**
     * Get the representation of multiple items
     * @param iterable<string|int|BackedEnum> $items
     * @return string
     */
    protected static final function formatCronItems(iterable $items) : string
    {
        $ret = '';
        foreach ($items as $item) {
            $ret .= ',' . static::formatCronItem($item);
        }

        return substr($ret, 1);
    }


    /**
     * Get the representation of cron item
     * @param string|int|BackedEnum $item
     * @return string
     */
    protected static final function formatCronItem(string|int|BackedEnum $item) : string
    {
        if ($item instanceof BackedEnum) $item = $item->value;

        return "$item";
    }


    /**
     * Get time components
     * @param string $time
     * @return array<int>
     * @throws SafetyCommonException
     */
    protected static final function getTimeComponents(string $time) : array
    {
        $components = explode(':', $time);
        if (count($components) !== 2) throw new InvalidDataException();

        $hour = IntegerParser::create()->withMin(0)->withMax(23)->parse($components[0], '#hour');
        $minute = IntegerParser::create()->withMin(0)->withMax(59)->parse($components[1], '#minute');

        return [$hour, $minute];
    }


    /**
     * @inheritDoc
     */
    public final function withCron(string $expression) : static
    {
        $this->cronExpression = $expression;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function withTimezone(string $timezone) : static
    {
        $this->timezone = $timezone;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withBackgroundRunning(bool $isRunInBackground = true) : static
    {
        $this->isRunInBackground = $isRunInBackground;
        return $this;
    }


    /**
     * Compile current definition into entry
     * @return ScheduledEntry
     * @throws UnsupportedException
     * @internal
     */
    public final function _compile() : ScheduledEntry
    {
        if (!$this->runner instanceof ScheduleRunner) throw new UnsupportedValueException($this->runner);

        return new ScheduledEntry($this->cronExpression, $this->timezone, $this->runner, $this->isRunInBackground);
    }


    /**
     * Splice the value into the CRON expression
     * @param iterable $segmentExpressions
     * @return $this
     * @throws SafetyCommonException
     */
    protected final function cronSplice(iterable $segmentExpressions) : static
    {
        $segments = explode(' ', $this->cronExpression);

        foreach ($segmentExpressions as $position => $segmentExpression) {
            if ($position < 1 || $position > 5) throw new InvalidDataException();
            $segments[$position - 1] = $segmentExpression;
        }

        return $this->withCron(implode(' ', $segments));
    }
}