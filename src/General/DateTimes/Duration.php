<?php

namespace Magpie\General\DateTimes;

use Carbon\CarbonInterval;
use Magpie\Codecs\Concepts\PrettyFormattable;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\PrecisionScalable;
use Magpie\General\DateTimes\Specific\DurationInMicroseconds;
use Magpie\General\DateTimes\Specific\DurationInMilliseconds;
use Magpie\General\DateTimes\Specific\DurationInNanoseconds;
use Magpie\General\DateTimes\Specific\DurationInSeconds;
use Magpie\General\MultiPrecision;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\General\Traits\CommonPrecisionAtScaleArithmetic;

/**
 * Representation of a duration
 */
abstract class Duration implements PrecisionScalable, PrettyFormattable, Packable
{
    use CommonPackable;
    use CommonPrecisionAtScaleArithmetic;


    /**
     * Get the number of duration in seconds (if precision higher than seconds, round to lower second)
     * @return int
     */
    public function getSeconds() : int
    {
        return $this->getValueAtPrecisionScale(0);
    }


    /**
     * Unit name
     * @return string
     */
    public abstract function getUnitName() : string;


    /**
     * Express in CarbonInterval
     * @return CarbonInterval
     * @throws SafetyCommonException
     */
    public abstract function toCarbonInterval() : CarbonInterval;


    /**
     * @inheritDoc
     */
    public function prettyFormat() : string
    {
        return $this->getBaseValue() . $this->getUnitName();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->baseValue = $this->getBaseValue();
        $ret->unitName = $this->getUnitName();
    }


    /**
     * Standard to accept duration, with an integer optionally means duration in seconds
     * @param int|self|null $spec
     * @return self|null
     */
    public static function accept(int|self|null $spec) : ?self
    {
        if ($spec === null) return null;

        if ($spec instanceof Duration) return static::translateToPrecision($spec);

        return static::inSeconds($spec);
    }


    /**
     * Translate the duration in specific precision
     * @param Duration $spec
     * @return static
     */
    protected static function translateToPrecision(self $spec) : self
    {
        return $spec;
    }


    /**
     * Duration specification in number of days
     * @param int $days
     * @return self
     */
    public static function inDays(int $days) : self
    {
        return new DurationInSeconds($days * 86400);
    }


    /**
     * Duration specification in number of hours
     * @param int $hours
     * @return self
     */
    public static function inHours(int $hours) : self
    {
        return new DurationInSeconds($hours * 3600);
    }


    /**
     * Duration specification in number of minutes
     * @param int $minutes
     * @return self
     */
    public static function inMinutes(int $minutes) : self
    {
        return new DurationInSeconds($minutes * 60);
    }


    /**
     * Duration specification in number of seconds
     * @param int $seconds
     * @return self
     */
    public static function inSeconds(int $seconds) : self
    {
        return new DurationInSeconds($seconds);
    }


    /**
     * Duration specification in number of milliseconds
     * @param int $milliseconds
     * @return self
     */
    public static function inMilliseconds(int $milliseconds) : self
    {
        return new DurationInMilliseconds($milliseconds);
    }


    /**
     * Duration specification in number of microseconds
     * @param int $microseconds
     * @return self
     */
    public static function inMicroseconds(int $microseconds) : self
    {
        return new DurationInMicroseconds($microseconds);
    }


    /**
     * Duration specification in number of nanoseconds
     * @param int $nanoseconds
     * @return self
     */
    public static function inNanoseconds(int $nanoseconds) : self
    {
        return new DurationInNanoseconds($nanoseconds);
    }


    /**
     * Create a parser to parse in duration specified in second precision
     * @return Parser<self>
     */
    public static function createSecondParser() : Parser
    {
        return ClosureParser::create(function(mixed $value, ?string $hintName) : self {
            $value = IntegerParser::create()->withMin(0)->parse($value, $hintName);
            return self::inSeconds($value);
        });
    }


    /**
     * @inheritDoc
     */
    public static function inPrecision(int|float $value, int $precision) : self
    {
        $matchedValue = MultiPrecision::matchSpecificPrecision($value, $precision, [-9, -6, -3, 0], $matchedPrecision);

        return match ($matchedPrecision) {
            -9 => static::inNanoseconds($matchedValue),
            -6 => static::inMicroseconds($matchedValue),
            -3 => static::inMilliseconds($matchedValue),
            0 => static::inSeconds($matchedValue),
            default => static::inSeconds(0),    // Unexpected
        };
    }
}