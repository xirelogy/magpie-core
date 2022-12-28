<?php

namespace Magpie\General\Traits;

use Magpie\General\Concepts\PrecisionScalable;
use Magpie\General\MultiPrecision;

/**
 * Support arithmetic for value at multiple precisions
 * @requires \Magpie\General\Concepts\PrecisionScalable
 */
trait CommonPrecisionAtScaleArithmetic
{
    use CommonPrecisionAtScale;


    /**
     * Arithmetic add
     * @param PrecisionScalable $rhs
     * @return PrecisionScalable
     */
    public function add(PrecisionScalable $rhs) : PrecisionScalable
    {
        [$newLhs, $newRhs] = MultiPrecision::selectPrecision($newPrecision, $this, $rhs);

        return static::inPrecision($newLhs + $newRhs, $newPrecision);
    }


    /**
     * Arithmetic subtract
     * @param PrecisionScalable $rhs
     * @return PrecisionScalable
     */
    public function subtract(PrecisionScalable $rhs) : PrecisionScalable
    {
        [$newLhs, $newRhs] = MultiPrecision::selectPrecision($newPrecision, $this, $rhs);

        return static::inPrecision($newLhs - $newRhs, $newPrecision);
    }
}