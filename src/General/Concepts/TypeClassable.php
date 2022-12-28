<?php

namespace Magpie\General\Concepts;

/**
 * Anything that support type classes
 */
interface TypeClassable
{
    /**
     * Type class
     * @return string
     */
    public static function getTypeClass() : string;
}