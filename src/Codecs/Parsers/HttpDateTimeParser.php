<?php

namespace Magpie\Codecs\Parsers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\ParseFailedException;
use Magpie\Locales\Concepts\Localizable;

/**
 * HTTP-date parser
 * @extends CreatableParser<CarbonInterface>
 */
class HttpDateTimeParser extends CreatableParser
{
    /**
     * @var bool If obsolete format is rejected
     */
    protected bool $isObsoleteRejected = false;


    /**
     * If obsolete format (RFC850/asctime) is rejected
     * @param bool $isRejected
     * @return $this
     */
    public function withObsoleteRejected(bool $isRejected = true) : static
    {
        $this->isObsoleteRejected = $isRejected;
        return $this;
    }


    /**
     * @inheritDoc
     * @link https://www.rfc-editor.org/rfc/rfc9110#http.date
     */
    protected function onParse(mixed $value, ?string $hintName) : CarbonInterface
    {
        $value = StringParser::createTrimEmptyAsNull()->parse($value, $hintName);

        $ret = static::parseImfDateTime($value);
        if ($ret !== null) return $ret;

        if (!$this->isObsoleteRejected) {
            $ret = static::parseRfc850DateTime($value);
            if ($ret !== null) return $ret;

            $ret = static::parseAscTime($value);
            if ($ret !== null) return $ret;
        }

        throw new InvalidDataException();
    }


    /**
     * Parse according to 'IMF-fixdate' format.
     * Extended to also support optional format with dashes between day/month/year.
     * @param string $value
     * @return CarbonInterface|null
     * @throws Exception
     * @link https://www.rfc-editor.org/rfc/rfc5322#section-3.3
     */
    protected static function parseImfDateTime(string $value) : ?CarbonInterface
    {
        $result = preg_match('/([A-Z][a-z][a-z]), (\d{2})[\- ]([A-Z][a-z][a-z])[\- ](\d{4}) (\d{2}):(\d{2}):(\d{2}) (GMT)$/', $value, $matches);
        if ($result !== 1) return null;

        [$dummy, $dayOfWeek, $day, $month, $year, $hour, $minute, $second, $timezone] = $matches;
        _used($dummy);

        if ($timezone !== 'GMT') throw new ParseFailedException(static::formatInvalidMessage(_l('timezone')));

        $dayOfWeek = static::translateDayOfWeek3($dayOfWeek);
        if ($dayOfWeek === null) throw new ParseFailedException(static::formatInvalidMessage(_l('day of week')));

        $month = static::translateMonth3($month);
        if ($month === null) throw new ParseFailedException(static::formatInvalidMessage(_l('month')));

        $year = intval($year);

        return static::checkAndCreateReturn($dayOfWeek, $year, $month, $day, $hour, $minute, $second);
    }


    /**
     * Parse according to RFC850 format
     * @param string $value
     * @return CarbonInterface|null
     * @throws Exception
     * @link https://www.rfc-editor.org/rfc/rfc850#section-2.1.4
     */
    protected static function parseRfc850DateTime(string $value) : ?CarbonInterface
    {
        $result = preg_match('/([A-Z][a-z]*), (\d{2})-([A-Z][a-z][a-z])-(\d{2}) (\d{2}):(\d{2}):(\d{2}) (GMT)$/', $value, $matches);
        if ($result !== 1) return null;

        [$dummy, $dayOfWeek, $day, $month, $year, $hour, $minute, $second, $timezone] = $matches;
        _used($dummy);

        if ($timezone !== 'GMT') throw new ParseFailedException(static::formatInvalidMessage(_l('timezone')));

        $dayOfWeek = static::translateDayOfWeekFull($dayOfWeek);
        if ($dayOfWeek === null) throw new ParseFailedException(static::formatInvalidMessage(_l('day of week')));

        $month = static::translateMonth3($month);
        if ($month === null) throw new ParseFailedException(static::formatInvalidMessage(_l('month')));

        $year = intval($year);
        if ($year < 100) {
            $year = ($year < 70) ? (2000 + $year) : (1900 + $year);
        }

        return static::checkAndCreateReturn($dayOfWeek, $year, $month, $day, $hour, $minute, $second);
    }


    /**
     * Parse according to asctime() format
     * @param string $value
     * @return CarbonInterface|null
     * @throws Exception
     */
    protected static function parseAscTime(string $value) : ?CarbonInterface
    {
        $result = preg_match('/([A-Z][a-z][a-z]) ([A-Z][a-z][a-z]) ([ \d]\d) (\d{2}):(\d{2}):(\d{2}) (\d{4})$/', $value, $matches);
        if ($result !== 1) return null;

        [$dummy, $dayOfWeek, $month, $day, $hour, $minute, $second, $year] = $matches;
        _used($dummy);

        $dayOfWeek = static::translateDayOfWeek3($dayOfWeek);
        if ($dayOfWeek === null) throw new ParseFailedException(static::formatInvalidMessage(_l('day of week')));

        $month = static::translateMonth3($month);
        if ($month === null) throw new ParseFailedException(static::formatInvalidMessage(_l('month')));

        $year = intval($year);
        $day = trim($day);

        return static::checkAndCreateReturn($dayOfWeek, $year, $month, $day, $hour, $minute, $second);
    }


    /**
     * Check and create return date time
     * @param int $dayOfWeek
     * @param int $year
     * @param int $month
     * @param string $day
     * @param string $hour
     * @param string $minute
     * @param string $second
     * @return CarbonInterface
     * @throws Exception
     */
    protected static function checkAndCreateReturn(int $dayOfWeek, int $year, int $month, string $day, string $hour, string $minute, string $second) : CarbonInterface
    {
        $maxDay = static::getMaxDay($year, $month);
        if ($maxDay < 1) throw new ParseFailedException(static::formatInvalidMessage(_l('year/month combination')));

        $day = intval($day);
        if ($day < 1 || $day > $maxDay) throw new ParseFailedException(static::formatInvalidMessage(_l('day')));

        $hour = intval($hour);
        if ($hour > 23) throw new ParseFailedException(static::formatInvalidMessage(_l('hour')));
        $minute = intval($minute);
        if ($minute > 59) throw new ParseFailedException(static::formatInvalidMessage(_l('minute')));
        $second = intval($second);
        if ($second > 59) throw new ParseFailedException(static::formatInvalidMessage(_l('second')));

        $ret = Carbon::create($year, $month, $day, $hour, $minute, $second, 'UTC');
        if ($ret->dayOfWeek !== $dayOfWeek) throw new ParseFailedException(_l('wrong day of week'));

        return $ret;
    }


    /**
     * Translate day of week (3-letters)
     * @param string $value
     * @return int|null
     */
    protected static function translateDayOfWeek3(string $value) : ?int
    {
        return match(strtolower($value)) {
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
            default => null,
        };
    }


    /**
     * Translate day of week (full)
     * @param string $value
     * @return int|null
     */
    protected static function translateDayOfWeekFull(string $value) : ?int
    {
        return match(strtolower($value)) {
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            default => null,
        };
    }


    /**
     * Translate month (3-letters)
     * @param string $value
     * @return int|null
     */
    protected static function translateMonth3(string $value) : ?int
    {
        return match(strtolower($value)) {
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            default => null,
        };
    }


    /**
     * Get the maximum day number for given year and month
     * @param int $year
     * @param int $month
     * @return int
     */
    protected static function getMaxDay(int $year, int $month) : int
    {
        return match ($month) {
            1, 3, 5, 7, 8, 10, 12 => 31,
            4, 6, 9, 11 => 30,
            2 => static::isLeapYear($year) ? 29 : 28,
            default => 0,
        };
    }


    /**
     * Check if given year is leap year
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
     * Format message for invalid subject
     * @param string|Localizable $subject
     * @return string|Localizable
     */
    protected static function formatInvalidMessage(string|Localizable $subject) : string|Localizable
    {
        return _format_l('invalid data', 'invalid {{0}}', $subject);
    }
}