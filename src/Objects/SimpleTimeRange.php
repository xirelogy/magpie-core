<?php

namespace Magpie\Objects;

use Carbon\CarbonInterface;
use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * A simple time range
 */
class SimpleTimeRange extends CommonObject
{
    use CommonObjectPackAll;

    /**
     * @var CarbonInterface|null Start time (inclusive)
     */
    public readonly ?CarbonInterface $startAt;
    /**
     * @var CarbonInterface|null End time (non-inclusive)
     */
    public readonly ?CarbonInterface $endAt;


    /**
     * Constructor
     * @param CarbonInterface|null $startAt
     * @param CarbonInterface|null $endAt
     */
    public function __construct(?CarbonInterface $startAt, ?CarbonInterface $endAt)
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt;
    }


    /**
     * If the current time range is valid
     * @return bool
     */
    public function isValid() : bool
    {
        if ($this->startAt === null && $this->endAt === null) return false;

        if ($this->startAt !== null && $this->endAt !== null) {
            if ($this->startAt > $this->endAt) return false;
        }

        return true;
    }
}