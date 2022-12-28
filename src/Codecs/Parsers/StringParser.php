<?php

namespace Magpie\Codecs\Parsers;

use Closure;
use Exception;
use Magpie\Codecs\Parsers\Exceptions\CannotBeHandledAsStringParseFailedException;
use Stringable;

/**
 * String parser
 * @extends CreatableParser<string|null>
 */
class StringParser extends CreatableParser
{
    /**
     * @var bool If string is trimmed
     */
    protected bool $isTrimming = true;
    /**
     * @var bool If empty string is treated as null
     */
    protected bool $isEmptyAsNull = false;
    /**
     * @var Closure|null Pre-processor function
     */
    protected ?Closure $preprocessorFn = null;


    /**
     * With string trimmed automatically
     * @param bool $isTrimming
     * @return $this
     */
    public function withTrimming(bool $isTrimming = true) : static
    {
        $this->isTrimming = $isTrimming;
        return $this;
    }


    /**
     * With empty strings treated as null
     * @param bool $isEmptyAsNull
     * @return $this
     */
    public function withEmptyAsNull(bool $isEmptyAsNull = true) : static
    {
        $this->isEmptyAsNull = $isEmptyAsNull;
        return $this;
    }


    /**
     * With specific preprocessor
     * @param callable(string):string $fn
     * @return $this
     */
    public function withPreprocessor(callable $fn) : static
    {
        $this->preprocessorFn = $fn;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : ?string
    {
        $value = $this->onParseString($value, $hintName);

        if ($this->preprocessorFn !== null) $value = ($this->preprocessorFn)($value);

        if ($this->isTrimming) $value = trim($value);

        if ($this->isEmptyAsNull && $value === '') return null;

        return $value;
    }


    /**
     * Parse as a string
     * @param mixed $value
     * @param string|null $hintName
     * @return string|null
     * @throws Exception
     */
    protected function onParseString(mixed $value, ?string $hintName) : ?string
    {
        _used($hintName);

        if ($value === null) return '';

        if (is_string($value)) return $value;
        if (is_bool($value)) return ($value ? 'true' : 'false');    // Special processing for boolean
        if (is_scalar($value)) return "$value";

        if ($value instanceof Stringable) return $value;
        if (is_object($value) && method_exists($value, '__toString')) return $value->__toString();

        throw new CannotBeHandledAsStringParseFailedException();
    }
}