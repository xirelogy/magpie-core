<?php

namespace Magpie\System\HardCore;

use Magpie\General\Traits\StaticClass;

/**
 * Stack traversal support
 */
class StackTraversal
{
    use StaticClass;


    /**
     * Get the closest item on the stack with a class name
     * @param array<array> $stackTraces
     * @return string|null
     */
    public static function getLastClassName(array $stackTraces) : ?string
    {
        foreach ($stackTraces as $stackTrace) {
            if (array_key_exists('class', $stackTrace)) return $stackTrace['class'];
        }

        return null;
    }
}