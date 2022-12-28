<?php

namespace Magpie\Codecs\Parsers;

use Closure;
use Exception;
use Magpie\Codecs\Traits\CommonChainableParser;
use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\ParseFailedException;
use Magpie\General\Sugars\Quote;

/**
 * Array parser
 * @extends CreatableParser<array|null>
 * @implements ChainableParser<array|null>
 */
class ArrayParser extends CreatableParser implements ChainableParser
{
    use CommonParser;
    use CommonChainableParser;

    /**
     * @var Closure|null Post-processing function
     */
    protected ?Closure $postProcessorFn = null;
    /**
     * @var bool If empty array rejected as invalid
     */
    protected bool $isEmptyRejected = false;
    /**
     * @var bool If empty array treated as null
     */
    protected bool $isEmptyAsNull = false;


    /**
     * Specify the next level parser which is an object
     * @param Parser|null $subChainParser
     * @return $this
     */
    public function withChainObject(?Parser $subChainParser = null) : static
    {
        $chainParser = ObjectParser::create();
        if ($subChainParser !== null) $chainParser->withChain($subChainParser);

        return $this->withChain($chainParser);
    }


    /**
     * Specify post-processing function after array is available, but before
     * safety checks.
     * @param callable(array):array $fn
     * @return $this
     */
    public function withPostProcessor(callable $fn) : static
    {
        $this->postProcessorFn = $fn;
        return $this;
    }


    /**
     * Specify if empty array rejected as invalid
     * @param bool $isEmptyRejected
     * @return $this
     */
    public function withEmptyRejected(bool $isEmptyRejected = true) : static
    {
        $this->isEmptyRejected = $isEmptyRejected;
        return $this;
    }


    /**
     * Specify if empty array treated as null
     * @param bool $isEmptyAsNull
     * @return $this
     */
    public function withEmptyAsNull(bool $isEmptyAsNull = true) : static
    {
        $this->isEmptyAsNull = $isEmptyAsNull;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : ?array
    {
        $value = $this->onParseArray($value, $hintName);

        if ($this->postProcessorFn !== null) $value = ($this->postProcessorFn)($value);

        if ( $this->isEmptyRejected && count($value) <= 0) {
            throw new ParseFailedException(_l('Array is empty'));
        }

        if ($this->isEmptyAsNull && count($value) <= 0) {
            $value = null;
        }

        return $value;
    }


    /**
     * Parse as array
     * @param mixed $value
     * @param string|null $hintName
     * @return array
     * @throws Exception
     */
    protected function onParseArray(mixed $value, ?string $hintName) : array
    {
        if (is_iterable($value)) {
            $value = iter_flatten($value);
            if ($this->chainParser === null) return $value;

            // Apply the chain parser
            $ret = [];
            foreach ($value as $index => $subValue) {
                $nextHintName = static::makeNextHintName($hintName, $index);
                $ret[] = $this->chainParser->parse($subValue, $nextHintName);
            }

            return $ret;
        }

        throw new ParseFailedException(_l('Not an array'));
    }


    /**
     * Create next level hint name
     * @param string|null $hintName
     * @param string|int $index
     * @return string
     */
    protected static function makeNextHintName(?string $hintName, string|int $index) : string
    {
        if ($hintName === null) {
            return Quote::square($index);
        } else {
            return $hintName . Quote::square($index);  // Purposely using concatenation
        }
    }
}