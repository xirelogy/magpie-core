<?php

namespace Magpie\System\Concepts;

/**
 * Anything creatable by capturing from current context
 */
interface Capturable
{
    /**
     * Capture from current context
     * @return static
     */
    public static function capture() : static;
}