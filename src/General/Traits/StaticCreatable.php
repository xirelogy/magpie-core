<?php

namespace Magpie\General\Traits;

/**
 * Classes are expected to be created using create(), statically
 */
trait StaticCreatable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Create an instance
     * @return static
     */
    public static function create() : static
    {
        return new static();
    }
}

