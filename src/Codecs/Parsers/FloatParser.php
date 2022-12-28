<?php

namespace Magpie\Codecs\Parsers;

use Exception;
use Magpie\Codecs\Impls\NumericRangeCheck;
use Magpie\Codecs\Parsers\Exceptions\NotNumberParseFailedException;

/**
 * Floating point number parser
 * @extends CreatableParser<float>
 */
class FloatParser extends CreatableParser
{
    /**
     * @var float|null Minimum value to be checked against
     */
    protected ?float $min = null;
    /**
     * @var float|null Maximum value to be checked against
     */
    protected ?float $max = null;
    /**
     * @var int|null Precision to round the floating point number to
     */
    protected ?int $precision = null;


    /**
     * With minimum value checked
     * @param float $min Minimum allowed value (inclusive)
     * @return $this
     */
    public function withMin(float $min) : static
    {
        $this->min = $min;
        return $this;
    }


    /**
     * With maximum value checked
     * @param float $max Maximum allowed value (inclusive)
     * @return $this
     */
    public function withMax(float $max) : static
    {
        $this->max = $max;
        return $this;
    }


    /**
     * With specific precision to be rounded to
     * @param int $precision
     * @return $this
     */
    public function withPrecision(int $precision) : static
    {
        $this->precision = $precision;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : float
    {
        $ret = $this->onParseFloat($value, $hintName);

        if ($this->precision !== null) {
            $ret = round($ret, $this->precision);
        }

        NumericRangeCheck::checkRange($ret, $this->min, $this->max);

        return $ret;
    }


    /**
     * Parse like floating point numbers
     * @param mixed $value
     * @param string|null $hintName
     * @return float
     * @throws Exception
     */
    protected function onParseFloat(mixed $value, ?string $hintName = null) : float
    {
        _used($hintName);

        if (is_int($value)) return floatval($value);
        if (is_float($value)) return $value;

        // Must be something that can be handled like a string
        $value = StringParser::create()->parse($value, $hintName);
        if (!is_numeric($value)) throw new NotNumberParseFailedException();

        return floatval($value);
    }
}