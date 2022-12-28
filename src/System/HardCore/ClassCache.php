<?php

namespace Magpie\System\HardCore;

use Magpie\General\Traits\StaticClass;

/**
 * Caches for class
 */
class ClassCache
{
    use StaticClass;


    /**
     * Cache directory for given class
     * @param object|class-string $spec
     * @return string
     */
    public static function getClassDirectory(object|string $spec) : string
    {
        $className = static::acceptClassName($spec);
        $cleanedClassName = str_replace('\\', '-', $className);
        return project_path("/storage/caches/classes/$cleanedClassName");
    }


    /**
     * Accept class name
     * @param object|class-string $spec
     * @return string
     */
    protected static function acceptClassName(object|string $spec) : string
    {
        return is_string($spec) ? $spec : $spec::class;
    }
}