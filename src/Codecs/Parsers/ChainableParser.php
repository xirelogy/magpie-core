<?php

namespace Magpie\Codecs\Parsers;

/**
 * Parser with next level parser chainable
 * @template T
 * @extends Parser<T>
 */
interface ChainableParser extends Parser
{
    /**
     * Specify the next level parser in chain
     * @param Parser $chainParser
     * @return $this
     */
    public function withChain(Parser $chainParser) : static;
}