<?php

namespace Magpie\General\DateTimes\Specific;

use Carbon\CarbonInterval;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\DateTimes\Duration;

/**
 * Representation of a duration with millisecond level resolution
 */
class DurationInMilliseconds extends Duration
{
    /**
     * Precision scale at current level
     */
    public const SCALE = -3;
    /**
     * @var int Number of milliseconds
     */
    public int $milliseconds;


    /**
     * Constructor
     * @param int $milliseconds Number of milliseconds
     */
    public function __construct(int $milliseconds)
    {
        $this->milliseconds = $milliseconds;
    }


    /**
     * @inheritDoc
     */
    public function getUnitName() : string
    {
        return 'ms';
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
        return $this->milliseconds;
    }


    /**
     * @inheritDoc
     */
    public function toCarbonInterval() : CarbonInterval
    {
        try {
            return CarbonInterval::milliseconds($this->milliseconds);
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