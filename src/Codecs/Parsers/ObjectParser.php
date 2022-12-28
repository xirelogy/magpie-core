<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\ParserHosts\ObjectParserHost;
use Magpie\Codecs\Traits\CommonChainableParser;
use Magpie\Codecs\Traits\CommonContextableParser;
use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ParseFailedException;

/**
 * Object parser
 * @extends CreatableParser<mixed>
 * @implements ChainableParser<mixed>
 * @implements ContextableParser<mixed>
 */
class ObjectParser extends CreatableParser implements ChainableParser, ContextableParser
{
    use CommonParser;
    use CommonChainableParser;
    use CommonContextableParser;


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : mixed
    {
        if (!is_object($value)) throw new ParseFailedException(_l('Not an object'));

        // Setup the prefix
        $prefix = $hintName;

        // Create the parser host and parse whenever needed
        $ret = $this->createObjectParserHost($value, $prefix);

        if ($this->chainParser !== null) {
            $ret = $this->chainParser->parse($ret, $prefix);
        }

        return $ret;
    }


    /**
     * Create the object's parser host for given host value and prefix
     * @param object $value
     * @param string|null $prefix
     * @return ObjectParserHost
     */
    protected function createObjectParserHost(object $value, ?string $prefix) : ObjectParserHost
    {
        return new ObjectParserHost($value, $prefix);
    }
}