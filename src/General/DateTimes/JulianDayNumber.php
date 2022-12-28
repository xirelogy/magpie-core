<?php

namespace Magpie\General\DateTimes;

use Carbon\CarbonInterface;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\Traits\StaticClass;
use Magpie\Objects\SimpleDate;
use Magpie\Objects\SimpleDateComponents;

/**
 * Conversion between date/time and Julian day number
 * @link https://en.wikipedia.org/wiki/Julian_day
 */
class JulianDayNumber
{
    use StaticClass;

    /**
     * The reference timezone where Julian day number is calculated on
     */
    protected const TIMEZONE = 'UTC';


    /**
     * Encode date into Julian day number (ignoring time)
     * @param SimpleDate|SimpleDateComponents|CarbonInterface $value
     * @return int
     */
    public static function encodeDate(SimpleDate|SimpleDateComponents|CarbonInterface $value) : int
    {
        if ($value instanceof CarbonInterface) {
            $value = SimpleDate::fromTime($value);
        }

        if ($value instanceof SimpleDate) {
            $value = $value->getComponents();
        }

        $y = $value->year;
        $m = $value->month;
        $d = $value->day;

        $r = (1461 * ($y + 4800 + (($m - 14) / 12))) / 4
            + (367 * ($m - 2 - 12 * (($m - 14) / 12))) / 12
            - (3 * (($y + 4900 + ($m - 14) / 12) / 100)) / 4
            + $d - 32075;

        return floor($r);
    }


    /**
     * Encode time into Julian day number
     * @param CarbonInterface $value
     * @return float
     */
    public static function encode(CarbonInterface $value) : float
    {
        $value = $value->toImmutable()->setTimezone(static::TIMEZONE);
        $baseNumber = static::encodeDate($value);

        $h = $value->hour;
        $m = $value->minute;
        $s = $value->second;

        return $baseNumber + (($h - 12) / 24) + ($m / 1440) + ($s / 86400);
    }


    /**
     * Decode for date from Julian day number (ignoring time)
     * @param int $value
     * @return SimpleDate
     * @throws InvalidDataException
     */
    public static function decodeDate(int $value) : SimpleDate
    {
        $y = 4716;
        $j = 1401;
        $m = 2;
        $n = 12;
        $r = 4;
        $p = 1461;
        $v = 3;
        $u = 5;
        $s = 153;
        $w = 2;
        $B = 274277;
        $C = -38;

        $J = $value;

        $f = $J + $j + floor((floor((4 * $J + $B) / 146097) * 3) / 4) + $C;

        $e = $r * $f + $v;
        $g = floor(($e % $p) / $r);
        $h = $u * $g + $w;

        $D = floor(($h % $s) / $u) + 1;
        $M = ((floor($h / $s) + $m) % $n) + 1;
        $Y = floor($e / $p) - $y + floor(($n + $m - $M) / $n);

        return SimpleDate::from($Y, $M, $D);
    }


    /**
     * Decode for time from Julian day number
     * @param float $value
     * @param string|null $timezone
     * @return CarbonInterface
     * @throws InvalidDataException
     */
    public static function decode(float $value, ?string $timezone = null) : CarbonInterface
    {
        $baseDate = static::decodeDate(floor($value));
        $baseTime = $baseDate->expressedInTimezone(static::TIMEZONE)->setTime(12, 0); // UTC 12:00:00 is the zero reference

        $fraction = $value - floor($value);
        $secs = round($fraction * 86400);  // Total number of seconds since the base time
        $baseTime->addSeconds($secs);

        if ($timezone !== null) $baseTime->setTimezone($timezone);
        return $baseTime;
    }
}