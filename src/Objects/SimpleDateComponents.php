<?php

namespace Magpie\Objects;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Simple date components
 */
class SimpleDateComponents implements Packable
{
    use CommonPackable;


    /**
     * @var int Year
     */
    public int $year;
    /**
     * @var int Month
     */
    public int $month;
    /**
     * @var int Day
     */
    public int $day;


    /**
     * Constructor
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function __construct(int $year, int $month, int $day)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->year = $this->year;
        $ret->month = $this->month;
        $ret->day = $this->day;
    }
}