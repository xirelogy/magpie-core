<?php

namespace Magpie\Consoles\Inputs;

use Magpie\General\Concepts\TypeClassable;
use Stringable;

/**
 * Input prompt with option
 */
abstract class PromptWithOption implements Stringable, TypeClassable
{
    /**
     * @var Stringable|string Prompt
     */
    public readonly Stringable|string $prompt;


    /**
     * Constructor
     * @param Stringable|string $prompt
     */
    protected function __construct(Stringable|string $prompt)
    {
        $this->prompt = $prompt;
    }


    /**
     * @inheritDoc
     */
    public final function __toString() : string
    {
        if ($this->prompt instanceof Stringable) return $this->prompt->__toString();
        return $this->prompt;
    }


    /**
     * Create an instance for specific prompt
     * @param Stringable|string $prompt
     * @return static
     */
    public static function for(Stringable|string $prompt) : static
    {
        return new static($prompt);
    }
}