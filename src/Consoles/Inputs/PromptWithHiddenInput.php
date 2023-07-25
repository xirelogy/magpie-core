<?php

namespace Magpie\Consoles\Inputs;

/**
 * Specify that user input shall be hidden
 */
class PromptWithHiddenInput extends PromptWithOption
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'hidden-input';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}