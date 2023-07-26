<?php

namespace Magpie\Codecs\Parsers;

use Exception;
use Magpie\Codecs\Impls\NumericRangeCheck;
use Magpie\Codecs\Parsers\Exceptions\NotNumberParseFailedException;
use Magpie\Exceptions\ParseFailedException;

/**
 * Parse for integer
 * @extends CreatableParser<int>
 */
class IntegerParser extends CreatableParser
{
    /**
     * @var int|null Minimum value to be checked against
     */
    protected ?int $min = null;
    /**
     * @var int|null Maximum value to be checked against
     */
    protected ?int $max = null;
    /**
     * @var bool If expect to be strictly integers only (floating point numbers will be rejected)
     */
    protected bool $isStrict = false;


    /**
     * With minimum value checked
     * @param int $min Minimum allowed value (inclusive)
     * @return $this
     */
    public function withMin(int $min) : static
    {
        $this->min = $min;
        return $this;
    }


    /**
     * With maximum value checked
     * @param int $max Maximum allowed value (inclusive)
     * @return $this
     */
    public function withMax(int $max) : static
    {
        $this->max = $max;
        return $this;
    }


    /**
     * With value in range checked
     * @param int $min Minimum allowed value (inclusive)
     * @param int $max Maximum allowed value (inclusive)
     * @return $this
     */
    public function withRange(int $min, int $max) : static
    {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }


    /**
     * With specific strictness (floating point numbers will be rejected)
     * @param bool $isStrict
     * @return $this
     */
    public function withStrict(bool $isStrict = true) : static
    {
        $this->isStrict = $isStrict;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName = null) : int
    {
        $ret = $this->onParseInteger($value, $hintName);

        NumericRangeCheck::checkRange($ret, $this->min, $this->max);

        return $ret;
    }


    /**
     * Parse like integer
     * @param mixed $value
     * @param string|null $hintName
     * @return int
     * @throws Exception
     */
    protected function onParseInteger(mixed $value, ?string $hintName = null) : int
    {
        if (is_int($value)) return $value;
        if (is_float($value)) {
            if ($this->isStrict) throw new ParseFailedException(_l('Must be integer'));
            return (int)round($value);
        }

        // Must be something that can be handled like a string
        $value = StringParser::create()->parse($value, $hintName);
        if (!is_numeric($value)) throw new NotNumberParseFailedException();

        if (str_contains($value, '.')) return $this->onParseInteger(floatval($value), $hintName);
        return intval($value);
    }
}