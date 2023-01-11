<?php

namespace Magpie\Objects;

use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * Basic color specification in components of RGB (red, green, blue)
 */
class BasicRgbColor extends CommonObject
{
    use CommonObjectPackAll;

    /**
     * @var int Red component (0-255)
     */
    public int $r;
    /**
     * @var int Green component (0-255)
     */
    public int $g;
    /**
     * @var int Blue component (0-255)
     */
    public int $b;


    /**
     * Constructor
     * @param int $r
     * @param int $g
     * @param int $b
     */
    public function __construct(int $r = 0, int $g = 0, int $b = 0)
    {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }
}