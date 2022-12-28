<?php

namespace Magpie\Codecs\Traits;

/**
 * Common implementation for ContextableParser
 */
trait CommonContextableParser
{
    /**
     * @var mixed|null Parser context
     */
    protected mixed $context = null;


    /**
     * Specific the parser's context
     * @param mixed $context
     * @return $this
     */
    public function withContext(mixed $context) : static
    {
        $this->context = $context;
        return $this;
    }
}