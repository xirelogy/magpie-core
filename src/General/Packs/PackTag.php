<?php

namespace Magpie\General\Packs;

use Magpie\General\Traits\StaticClass;

/**
 * Shorthand pack tagger
 */
class PackTag
{
    use StaticClass;


    /**
     * May apply tag
     * @param mixed $target
     * @return PackTaggedFormatter
     */
    public static function for(mixed $target) : PackTaggedFormatter
    {
        return PackTaggedFormatter::for($target);
    }


    /**
     * Apply the 'header' tag
     * @param mixed $target
     * @return PackTaggedFormatter
     */
    public static function header(mixed $target) : PackTaggedFormatter
    {
        return PackTaggedFormatter::for($target, 'header');
    }


    /**
     * Apply the 'full' tag
     * @param mixed $target
     * @return PackTaggedFormatter
     */
    public static function full(mixed $target) : PackTaggedFormatter
    {
        return PackTaggedFormatter::for($target, 'full');
    }
}