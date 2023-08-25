<?php

namespace Magpie\HttpServer\Headers;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Traits\CommonParser;

/**
 * Parser for ColonSeparatedHeaderValue
 * @implements Parser<ColonSeparatedHeaderValue>
 */
abstract class ColonSeparatedHeaderValueParser implements Parser
{
    use CommonParser;

    /**
     * @var bool If the result keys are case-sensitive
     */
    protected bool $isCaseSensitive = false;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Specify if keys are case-sensitive
     * @param bool $isCaseSensitive
     * @return $this
     */
    public function withCaseSensitiveKeys(bool $isCaseSensitive = true) : static
    {
        $this->isCaseSensitive = $isCaseSensitive;
        return $this;
    }
}