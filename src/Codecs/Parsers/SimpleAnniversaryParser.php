<?php

namespace Magpie\Codecs\Parsers;

use Exception;
use Magpie\Objects\SimpleAnniversary;

/**
 * Parse for SimpleAnniversary
 * @extends CreatableParser<SimpleAnniversary>
 */
class SimpleAnniversaryParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : SimpleAnniversary
    {
        if ($value instanceof SimpleAnniversary) return $value;

        $value = StringParser::create()->parse($value, $hintName);

        $components = explode('-', $value);
        if (count($components) != 2) throw new Exception('Wrong number of components');

        $month = IntegerParser::create()->withMin(1)->withMax(12)->parse($components[0], static::detailedHintName($hintName, 'month'));

        $maxDays = static::getMaxDays($month);
        $day = IntegerParser::create()->withMin(1)->withMax($maxDays)->parse($components[1], static::detailedHintName($hintName, 'day'));

        return SimpleAnniversary::from($month, $day);
    }


    /**
     * Calculate maximum number of days for given month
     * @param int $month
     * @return int
     */
    protected static function getMaxDays(int $month) : int
    {
        return match ($month) {
            1, 3, 5, 7, 8, 10, 12 => 31,
            4, 6, 9, 11 => 30,
            2 => 29,
            default => 0,
        };
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