<?php

namespace Magpie\System\HardCore;

use Exception;
use Magpie\General\Traits\StaticClass;
use ReflectionClass;

/**
 * Class properties accessed from reflections
 */
class ClassReflection
{
    use StaticClass;


    /**
     * If given class is anonymous
     * @param object|string $className
     * @return bool
     */
    public static function isAnonymous(object|string $className) : bool
    {
        $className = is_object($className) ? $className::class : $className;
        return str_contains($className, "@anonymous\x00");
    }


    /**
     * If given class is an abstract class
     * @param object|class-string $className
     * @return bool
     */
    public static function isAbstract(object|string $className) : bool
    {
        try {
            $class = new ReflectionClass($className);
            return $class->isAbstract();
        } catch (Exception) {
            return false;
        }
    }


    /**
     * If given class is a concrete class
     * @param object|string $className
     * @return bool
     */
    public static function isConcrete(object|string $className) : bool
    {
        try {
            $class = new ReflectionClass($className);
            return !$class->isAbstract();
        } catch (Exception) {
            return false;
        }
    }
}