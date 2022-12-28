<?php

namespace Magpie\Codecs\Parsers;

/**
 * Parser with context specifiable
 * @template T
 * @extends Parser<T>
 */
interface ContextableParser extends Parser
{
    /**
     * Specific the parser's context
     * @param mixed $context
     * @return $this
     */
    public function withContext(mixed $context) : static;
}