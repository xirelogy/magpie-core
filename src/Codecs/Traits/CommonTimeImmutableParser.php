<?php

namespace Magpie\Codecs\Traits;

/**
 * Common implementation for parsers that support immutable date/time output
 */
trait CommonTimeImmutableParser
{
    /**
     * @var bool If the result should be provided as an immutable
     */
    protected bool $isImmutableOutput = false;


    /**
     * Specify output to be provided as immutable
     * @param bool $isImmutableOutput
     * @return $this
     */
    public function withImmutableOutput(bool $isImmutableOutput = true) : static
    {
        $this->isImmutableOutput = $isImmutableOutput;
        return $this;
    }
}