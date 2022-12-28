<?php

namespace Magpie\Objects;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Codecs\Concepts\PrettyFormattable;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\SimpleAnniversaryParser;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\SystemTimezone;

/**
 * A simple anniversary (recursive date every year), timezone is not considered
 */
class SimpleAnniversary implements PreferStringable, PrettyFormattable, ObjectParseable
{
    /**
     * Reference year
     */
    protected const REF_YEAR = 2000;
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
     * @param int $year
     * @return SimpleDateComponents
     */
    public function getComponents(int $year) : SimpleDateComponents
    {
        return new SimpleDateComponents($year, $this->baseValue->month, $this->baseValue->day);
    }


    /**
     * Express the date in particular timezone
     * @param int $year Specific year to express in
     * @param string|null $tz Timezone to expressed in, or the system's default timezone when omitted
     * @return CarbonInterface
     */
    public function expressedInTimezone(int $year, ?string $tz = null) : CarbonInterface
    {
        $tz = $tz ?? SystemTimezone::default();

        $components = $this->getComponents($year);
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
        return $this->baseValue->format('m-d');
    }


    /**
     * Create from given month and day
     * @param int $month
     * @param int $day
     * @return static
     * @throws InvalidDataException
     */
    public static function from(int $month, int $day) : static
    {
        $baseValue = Carbon::create(static::REF_YEAR, $month, $day, 0, 0, 0, static::TIMEZONE);

        if ($baseValue->month != $month) throw new InvalidDataException();
        if ($baseValue->day != $day) throw new InvalidDataException();

        return new static($baseValue);
    }


    /**
     * @inheritDoc
     */
    public static function createParser() : Parser
    {
        return SimpleAnniversaryParser::create();
    }
}