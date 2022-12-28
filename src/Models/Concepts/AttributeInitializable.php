<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * Initialization provider
 * @template T
 */
interface AttributeInitializable
{
    /**
     * Generate a value for current attribute
     * @return T
     * @throws SafetyCommonException
     */
    public static function generate() : mixed;
}