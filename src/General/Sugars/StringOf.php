<?php

namespace Magpie\General\Sugars;

use Magpie\General\Traits\StaticClass;

/**
 * Get the string representation of anything
 */
final class StringOf
{
    use StaticClass;


    /**
     * String representation of given target
     * @param mixed $target
     * @return string
     */
    public static function target(mixed $target) : string
    {
        if ($target === null) return _l('null');
        if (is_bool($target)) return ($target ? _l('true') : _l('false'));
        if (is_numeric($target)) return "$target";
        if (is_string($target)) return Quote::double($target);
        if (is_object($target) && enum_exists($target::class)) return static::ofEnum($target);

        if (is_array($target)) return _l('array');
        if (is_object($target)) return _format_safe(_l('object(\'{{0}}\')'), $target::class) ?? ('object' . Quote::bracket(Quote::single($target::class)));

        if (is_iterable($target)) return _l('iterable');
        if (is_callable($target)) return _l('callable');

        return _l('unknown');
    }


    /**
     * String representation of enum
     * @param object $target
     * @return string
     */
    protected static function ofEnum(object $target) : string
    {
        $ret = $target->value;
        return is_string($ret) ? Quote::double($ret) : "$ret";
    }
}