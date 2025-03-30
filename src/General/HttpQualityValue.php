<?php

namespace Magpie\General;

use Magpie\Objects\CommonObject;
use Magpie\Objects\Traits\CommonObjectPackAll;

/**
 * Value with associated q-values/q-factors (priority)
 */
class HttpQualityValue extends CommonObject
{
    use CommonObjectPackAll;

    /**
     * @var string Main value
     */
    public readonly string $value;
    /**
     * @var float Effective quality value
     */
    public readonly float $q;


    /**
     * Constructor
     * @param string $value
     * @param float $q
     */
    public function __construct(string $value, float $q)
    {
        $this->value = $value;
        $this->q = $q;
    }
}