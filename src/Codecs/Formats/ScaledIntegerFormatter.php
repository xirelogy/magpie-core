<?php

namespace Magpie\Codecs\Formats;

use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Format scaled integer into its actual decimal number
 */
class ScaledIntegerFormatter implements Formatter
{
    /**
     * @var int The scale-up factor for the value (must be positive and power of 10)
     */
    protected readonly int $unitFactor;
    /**
     * @var int Corresponding number of decimals
     */
    protected readonly int $decimals;
    /**
     * @var bool If to trim trailing zeros in the fraction part
     */
    protected bool $isTrim = false;
    /**
     * @var bool If to insert thousand-separators into the integer part
     */
    protected bool $isGroup = false;


    /**
     * Constructor
     * @param int $unitFactor
     * @throws SafetyCommonException
     */
    protected function __construct(int $unitFactor)
    {
        if ($unitFactor <= 0) throw new InvalidDataException(_l('unitFactor must be a positive integer'));

        $decimals = 0;
        $testFactor = $unitFactor;
        while ($testFactor > 1) {
            if ($testFactor % 10 !== 0) {
                throw new InvalidDataException(_l('unitFactor must be power of 10'));
            }
            $testFactor /= 10;
            ++$decimals;
        }

        // Store and save
        $this->unitFactor = $unitFactor;
        $this->decimals = $decimals;
    }


    /**
     * @inheritDoc
     */
    public final function format(mixed $value) : mixed
    {
        $testValue = static::getValue($value);
        if ($testValue !== null) return $this->onFormat($testValue);

        return $value;
    }


    /**
     * Format the given value
     * @param int $value
     * @return string
     */
    protected function onFormat(int $value) : string
    {
        // Get sign and absolute value
        $sign = $value < 0 ? '-' : '';
        $absValue = abs($value);

        // Split into integer and fractional parts
        $intPart = intdiv($absValue, $this->unitFactor);
        $fracPart = $absValue % $this->unitFactor;

        // Group the integer part if required
        $intRet = $this->isGroup
            ? number_format($intPart, 0, '', ',')
            : (string)$intPart;

        // Special case without fractional part
        if ($this->decimals === 0) {
            return $sign . $intRet;
        }

        // Pad the fractional part with leading zeros
        $fracRet = str_pad((string)$fracPart, $this->decimals, '0', STR_PAD_LEFT);

        if (!$this->isTrim) {
            // Fixed decimal: all decimal points are always shown
            return $sign . $intRet . '.' . $fracRet;
        } else {
            // Trim trailing zeros
            $trimmed = rtrim($fracRet, '0');
            if ($trimmed === '') {
                return $sign . $intRet; // All trimmed
            }

            return $sign . $intRet . $trimmed;
        }
    }


    /**
     * Specify if to trim trailing zeros in the fraction part
     * @param bool $isTrim
     * @return $this
     */
    public function withTrim(bool $isTrim = true) : static
    {
        $this->isTrim = $isTrim;
        return $this;
    }


    /**
     * Specify if to insert thousand-separators into the integer part
     * @param bool $isGroup
     * @return $this
     */
    public function withGroup(bool $isGroup = true) : static
    {
        $this->isGroup = $isGroup;
        return $this;
    }


    /**
     * Create an instance
     * @param int $unitFactor
     * @return static
     * @throws SafetyCommonException
     */
    public static function create(int $unitFactor) : static
    {
        return new static($unitFactor);
    }


    /**
     * Try to get a number that can be operated on
     * @param mixed $value
     * @return int|null
     */
    protected static function getValue(mixed $value) : ?int
    {
        if (is_int($value)) return $value;
        if (is_float($value)) return intval(floor($value));

        // Maybe a numeric string
        if (is_numeric($value)) return intval($value);

        return null;
    }
}