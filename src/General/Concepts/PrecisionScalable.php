<?php

namespace Magpie\General\Concepts;

/**
 * Scalable at multiple precision
 */
interface PrecisionScalable
{
    /**
     * Precision scale
     * @return int
     */
    public function getPrecisionScale() : int;


    /**
     * Base value at current scale
     * @return int|float
     */
    public function getBaseValue() : int|float;


    /**
     * Get value at given precision
     * @param int $scale
     * @return int
     */
    public function getValueAtPrecisionScale(int $scale) : int;


    /**
     * Specification in specific value and precision
     * @param int|float $value
     * @param int $precision
     * @return self
     */
    public static function inPrecision(int|float $value, int $precision) : self;
}