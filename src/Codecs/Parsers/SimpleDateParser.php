<?php

namespace Magpie\Codecs\Parsers;

use Exception;
use Magpie\Objects\SimpleDate;

/**
 * Parse for SimpleDate
 * @extends CreatableParser<SimpleDate>
 */
class SimpleDateParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : SimpleDate
    {
        if ($value instanceof SimpleDate) return $value;

        $value = StringParser::create()->parse($value, $hintName);

        $components = explode('-', $value);
        if (count($components) != 3) throw new Exception('Wrong number of components');

        $year = IntegerParser::create()->parse($components[0], static::detailedHintName($hintName, 'year'));
        $month = IntegerParser::create()->withMin(1)->withMax(12)->parse($components[1], static::detailedHintName($hintName, 'month'));

        $maxDays = static::getMaxDays($year, $month);
        $day = IntegerParser::create()->withMin(1)->withMax($maxDays)->parse($components[2], static::detailedHintName($hintName, 'day'));

        return SimpleDate::from($year, $month, $day);
    }


    /**
     * Calculate maximum number of days for given month of year
     * @param int $year
     * @param int $month
     * @return int
     */
    protected static function getMaxDays(int $year, int $month) : int
    {
        return match ($month) {
            1, 3, 5, 7, 8, 10, 12 => 31,
            4, 6, 9, 11 => 30,
            2 => static::isLeapYear($year) ? 29 : 28,
            default => 0,
        };
    }


    /**
     * Determine if given year is a leap year (Gregorian rules)
     * @param int $year
     * @return bool
     */
    protected static function isLeapYear(int $year) : bool
    {
        if ($year % 400 === 0) return true;
        if ($year % 100 === 0) return false;
        if ($year % 4 === 0) return true;
        return false;
    }


    /**
     * Get detailed hint name with the specific component
     * @param string|null $hintName
     * @param string $componentName
     * @return string
     */
    protected static function detailedHintName(?string $hintName, string $componentName) : string
    {
        if ($hintName === null) return $componentName;
        return "$hintName.$componentName";
    }
}