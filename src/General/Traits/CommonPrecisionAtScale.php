<?php

namespace Magpie\General\Traits;

use Magpie\General\DateTimes\Duration;
use Magpie\General\MultiPrecision;

/**
 * Support extracting value at multiple precision
 * @requires \Magpie\General\Concepts\PrecisionScalable
 */
trait CommonPrecisionAtScale
{
    /**
     * Get value at given precision
     * @param int $scale
     * @return int
     */
    public function getValueAtPrecisionScale(int $scale) : int
    {
        $diffScale = $this->getPrecisionScale() - $scale;
        return floor($this->getBaseValue() * pow(10, $diffScale));
    }


    /**
     * Unify the precision of all values
     * @param Duration ...$values
     * @return void
     */
    public static function unifyPrecision(self &...$values) : void
    {
        $newValues = MultiPrecision::selectPrecision($newPrecision, ...$values);

        for ($i = 0; $i < count($values); ++$i) {
            $values[$i] = static::inPrecision($newValues[$i], $newPrecision);
        }
    }
}