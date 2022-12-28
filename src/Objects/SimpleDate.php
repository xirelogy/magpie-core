<?php

namespace Magpie\Objects;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Codecs\Concepts\PrettyFormattable;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\SimpleDateParser;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\SystemTimezone;

/**
 * A simple date, without any time information, timezone is not considered
 */
class SimpleDate implements PreferStringable, PrettyFormattable, ObjectParseable
{
    /**
     * The reference timezone where base value is stored in
     */
    public const TIMEZONE = 'UTC';

    /**
     * @var CarbonInterface Underlying value stored in UTC
     */
    protected CarbonInterface $baseValue;


    /**
     * Constructor
     * @param CarbonInterface $baseValue
     */
    protected function __construct(CarbonInterface $baseValue)
    {
        $this->baseValue = $baseValue;
    }


    /**
     * Respective date components
     * @return SimpleDateComponents
     */
    public function getComponents() : SimpleDateComponents
    {
        return new SimpleDateComponents($this->baseValue->year, $this->baseValue->month, $this->baseValue->day);
    }


    /**
     * Express the date in particular timezone
     * @param string|null $tz Timezone to expressed in, or the system's default timezone when omitted
     * @return CarbonInterface
     */
    public function expressedInTimezone(?string $tz = null) : CarbonInterface
    {
        $tz = $tz ?? SystemTimezone::default();

        $components = $this->getComponents();
        return Carbon::create($components->year, $components->month, $components->day, 0, 0, 0, $tz);
    }


    /**
     * @inheritDoc
     */
    public function prettyFormat() : string
    {
        return $this->__toString();
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->baseValue->format('Y-m-d');
    }


    /**
     * Create from given year, month and day
     * @param int $year
     * @param int $month
     * @param int $day
     * @return static
     * @throws InvalidDataException
     */
    public static function from(int $year, int $month, int $day) : static
    {
        $baseValue = Carbon::create($year, $month, $day, 0, 0, 0, static::TIMEZONE);

        if ($baseValue->year != $year) throw new InvalidDataException();
        if ($baseValue->month != $month) throw new InvalidDataException();
        if ($baseValue->day != $day) throw new InvalidDataException();

        return new static($baseValue);
    }


    /**
     * Create from given time value
     * @param CarbonInterface $value
     * @param string|null $timezone
     * @return static
     */
    public static function fromTime(CarbonInterface $value, ?string $timezone = null) : static
    {
        $value = $value->toImmutable();
        if ($timezone !== null) $value = $value->setTimezone($timezone);

        $baseValue = Carbon::create($value->year, $value->month, $value->day, 0, 0, 0, static::TIMEZONE);

        return new static($baseValue);
    }


    /**
     * @inheritDoc
     */
    public static function createParser() : Parser
    {
        return SimpleDateParser::create();
    }
}