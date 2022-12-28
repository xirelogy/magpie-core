<?php

namespace Magpie\General;

use Magpie\General\Concepts\PrecisionScalable;
use Magpie\General\Traits\StaticClass;

/**
 * Multi-precision support
 */
class MultiPrecision
{
    use StaticClass;


    /**
     * Select best precision that covers all values
     * @param int|null $outPrecision
     * @param PrecisionScalable ...$values
     * @return array<int|float>
     */
    public static function selectPrecision(?int &$outPrecision, PrecisionScalable... $values) : array
    {
        $outPrecision = null;

        foreach ($values as $value) {
            $thisPrecision = $value->getPrecisionScale();

            if ($outPrecision === null) {
                $outPrecision = $thisPrecision;
            } else if ($outPrecision > $thisPrecision) {
                $outPrecision = $thisPrecision;
            }
        }

        $ret = [];
        foreach ($values as $value) {
            $ret[] = $value->getValueAtPrecisionScale($outPrecision);
        }

        return $ret;
    }


    /**
     * Try to match the best available precision from the target precisions, to find a precision
     * that loses the least accuracy.
     * @param PrecisionScalable $value Scalable value
     * @param array $precisions Valid precisions
     * @param int|null $outSelectedPrecision Selected precision
     * @return int
     */
    public static function matchPrecision(PrecisionScalable $value, array $precisions, ?int &$outSelectedPrecision) : int
    {
        // Fallback default
        if (count($precisions) < 0) return static::calculateAtPrecision($value, 0, $outSelectedPrecision);

        sort($precisions);

        $lastPrecision = null;
        foreach ($precisions as $precision) {
            if ($value->getPrecisionScale() < $precision) {
                if ($lastPrecision === null) {
                    return static::calculateAtPrecision($value, $precision, $outSelectedPrecision);
                } else {
                    return static::calculateAtPrecision($value, $lastPrecision, $outSelectedPrecision);
                }
            }
            $lastPrecision = $precision;
        }

        $lastPrecision = $precisions[count($precisions) - 1] ?? 0;
        return static::calculateAtPrecision($value, $lastPrecision, $outSelectedPrecision);
    }


    /**
     * Calculate at given precision
     * @param PrecisionScalable $value
     * @param int $precision
     * @param int|null $outSelectedPrecision
     * @return int
     */
    protected static function calculateAtPrecision(PrecisionScalable $value, int $precision, ?int &$outSelectedPrecision) : int
    {
        $outSelectedPrecision = $precision;

        return $value->getValueAtPrecisionScale($precision);
    }


    /**
     * Try to match the best available precision from the target precisions, to find a precision
     * that loses the least accuracy.
     * @param int|float $value Current value
     * @param int $valuePrecision Current value's precision
     * @param array $precisions Valid precisions
     * @param int|null $outSelectedPrecision Selected precision
     * @return int
     */
    public static function matchSpecificPrecision(int|float $value, int $valuePrecision, array $precisions, ?int &$outSelectedPrecision) : int
    {
        // Fallback default
        if (count($precisions) < 0) return static::calculateAtSpecificPrecision($value, $valuePrecision, 0, $outSelectedPrecision);

        sort($precisions);

        $lastPrecision = null;
        foreach ($precisions as $precision) {
            if ($valuePrecision < $precision) {
                if ($lastPrecision === null) {
                    return static::calculateAtSpecificPrecision($value, $valuePrecision, $precision, $outSelectedPrecision);
                } else {
                    return static::calculateAtSpecificPrecision($value, $valuePrecision, $lastPrecision, $outSelectedPrecision);
                }
            }
            $lastPrecision = $precision;
        }

        $lastPrecision = $precisions[count($precisions) - 1] ?? 0;
        return static::calculateAtSpecificPrecision($value, $valuePrecision, $lastPrecision, $outSelectedPrecision);
    }


    /**
     * Calculate at given precision
     * @param int|float $value
     * @param int $valuePrecision
     * @param int $precision
     * @param int|null $outSelectedPrecision
     * @return int
     */
    protected static function calculateAtSpecificPrecision(int|float $value, int $valuePrecision, int $precision, ?int &$outSelectedPrecision) : int
    {
        $outSelectedPrecision = $precision;

        $diffScale = $valuePrecision - $precision;
        return floor($value * pow(10, $diffScale));
    }
}