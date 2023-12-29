<?php

namespace Magpie\Codecs\Formats;

use Carbon\CarbonInterface;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Magpie\Codecs\Concepts\PrettyFormattable;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Sugars\Excepts;

/**
 * The target is converted to an output format which is more readable to humans
 */
class PrettyGeneralFormatter extends GeneralFormatter
{
    /**
     * Date/time format to be used
     */
    protected const DATETIME_FORMAT = 'Y-m-d H:i:s O';

    /**
     * @var string Selected timezone
     */
    protected string $timezone;


    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->timezone = SystemTimezone::default();
    }


    /**
     * Specify the timezone to be used for date/time values
     * @param string $timezone
     * @return $this
     */
    public function withTimezone(string $timezone) : static
    {
        $this->timezone = $timezone;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function format(mixed $value) : mixed
    {
        if ($value instanceof PrettyFormattable) {
            return $value->prettyFormat();
        }

        if ($value instanceof CarbonInterface) {
            return $value->toImmutable()
                ->setTimezone($this->timezone)
                ->format(static::DATETIME_FORMAT);
        }

        if ($value instanceof DateTimeInterface) {
            return Excepts::noThrow(fn () => DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new DateTimeZone($this->timezone))
                ->format(static::DATETIME_FORMAT),
                '<err>');
        }

        return parent::format($value);
    }
}