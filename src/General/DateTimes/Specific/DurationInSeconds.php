<?php

namespace Magpie\General\DateTimes\Specific;

use Carbon\CarbonInterval;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\Duration;

/**
 * Representation of a duration with second level resolution
 */
class DurationInSeconds extends Duration
{
    /**
     * Precision scale at current level
     */
    public const SCALE = 0;
    /**
     * @var int Number of seconds
     */
    public int $seconds;


    /**
     * Constructor
     * @param int $seconds Number of seconds
     */
    public function __construct(int $seconds)
    {
        $this->seconds = $seconds;
    }


    /**
     * @inheritDoc
     */
    public function getUnitName() : string
    {
        return 's';
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
        return $this->seconds;
    }


    /**
     * @inheritDoc
     */
    public function toCarbonInterval() : CarbonInterval
    {
        try {
            return CarbonInterval::seconds($this->seconds);
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