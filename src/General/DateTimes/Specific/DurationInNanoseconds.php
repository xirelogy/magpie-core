<?php

namespace Magpie\General\DateTimes\Specific;

use Carbon\CarbonInterval;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\Duration;

/**
 * Representation of a duration with nanosecond level resolution
 */
class DurationInNanoseconds extends Duration
{
    /**
     * Precision scale at current level
     */
    public const SCALE = -9;
    /**
     * @var int Number of nanoseconds
     */
    public int $nanoseconds;


    /**
     * Constructor
     * @param int $nanoseconds Number of nanoseconds
     */
    public function __construct(int $nanoseconds)
    {
        $this->nanoseconds = $nanoseconds;
    }


    /**
     * @inheritDoc
     */
    public function getUnitName() : string
    {
        return 'ns';
    }


    /**
     * @inheritDoc
     */
    public function getPrecisionScale() : int
    {
        return static::SCALE;
    }


    /**
     * @inheritDoc
     */
    public function getBaseValue() : int
    {
        return $this->nanoseconds;
    }


    /**
     * @inheritDoc
     */
    public function toCarbonInterval() : CarbonInterval
    {
        try {
            return CarbonInterval::microseconds(floor($this->nanoseconds / 1000));
        } catch (Exception $ex) {
            throw new InvalidDataException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    protected static function translateToPrecision(Duration $spec) : static
    {
        return new static($spec->getValueAtPrecisionScale(static::SCALE));
    }
}