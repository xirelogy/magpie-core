<?php

namespace Magpie\Codecs\Traits;

use Magpie\Codecs\Parsers\Parser;

/**
 * Common implementation for ChainableParser
 */
trait CommonChainableParser
{
    /**
     * @var Parser|null Next level parser in chain
     */
    protected ?Parser $chainParser = null;


    /**
     * Specify the next level parser in chain
     * @param Parser $chainParser
     * @return $this
     */
    public function withChain(Parser $chainParser) : static
    {
        $this->chainParser = $chainParser;
        return $this;
    }
}