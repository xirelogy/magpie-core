<?php

namespace Magpie\General\DateTimes\Specific;

use Carbon\CarbonInterval;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\Duration;

/**
 * Representation of a duration with microsecond level resolution
 */
class DurationInMicroseconds extends Duration
{
    /**
     * Precision scale at current level
     */
    public const SCALE = -6;
    /**
     * @var int Number of microseconds
     */
    public int $microseconds;


    /**
     * Constructor
     * @param int $microseconds Number of microseconds
     */
    public function __construct(int $microseconds)
    {
        $this->microseconds = $microseconds;
    }


    /**
     * @inheritDoc
     */
    public function getUnitName() : string
    {
        return 'µs';
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
        return $this->microseconds;
    }


    /**
     * @inheritDoc
     */
    public function toCarbonInterval() : CarbonInterval
    {
        try {
            return CarbonInterval::microseconds($this->microseconds);
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