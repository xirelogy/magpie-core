<?php

namespace Magpie\Controllers\Strategies;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\General\Sugars\Excepts;

/**
 * Handle for editing from API
 */
class ApiEditor extends ApiModifier
{
    /**
     * @var bool If any changes registered
     */
    protected bool $isChanged = false;
    /**
     * @var array<string, string|int> Keys hidden from access
     */
    protected array $hiddenKeys = [];


    /**
     * Specify keys to be hidden
     * @param string|int $key
     * @return $this
     */
    public function withHiddenKey(string|int $key) : static
    {
        $this->hiddenKeys["$key"] = $key;
        return $this;
    }


    /**
     * Specify keys to be no longer hidden
     * @param string|int $key
     * @return $this
     */
    public function withHiddenKeyRemoved(string|int $key) : static
    {
        unset($this->hiddenKeys["$key"]);
        return $this;
    }


    /**
     * When key is provided, process the edit like a required key
     * @template T
     * @param string|int $key
     * @param Parser<T>|null $parser
     * @param callable(T):void $onValue
     * @return bool
     * @throws ArgumentException
     */
    public function whenRequires(string|int $key, ?Parser $parser, callable $onValue) : bool
    {
        if ($this->isKeyHidden($key)) return false;
        if (!$this->parserHost->has($key)) return false;

        $value = $this->parserHost->requires($key, $parser);
        Excepts::noThrow(fn () => $onValue($value));
        $this->isChanged = true;

        return true;
    }


    /**
     * When key is provided, process the edit like an optional key
     * @template T
     * @param string|int $key
     * @param Parser<T>|null $parser
     * @param callable(T):void $onValue
     * @return bool
     * @throws ArgumentException
     */
    public function whenOptional(string|int $key, ?Parser $parser, callable $onValue) : bool
    {
        if ($this->isKeyHidden($key)) return false;
        if (!$this->parserHost->has($key)) return false;

        $value = $this->parserHost->optional($key, $parser);
        Excepts::noThrow(fn () => $onValue($value));
        $this->isChanged = true;

        return true;
    }


    /**
     * A value is required (mandatory) from current editor's parser host
     * @template T
     * @param string|int $key Key to the value
     * @param Parser<T>|null $parser If supplied, parser to parse the value
     * @return T
     * @throws ArgumentException
     */
    public function directRequires(string|int $key, ?Parser $parser = null) : mixed
    {
        if ($this->isKeyHidden($key)) throw new MissingArgumentException($this->parserHost->fullKey($key));
        $ret = $this->parserHost->requires($key, $parser);
        $this->isChanged = true;
        return $ret;
    }


    /**
     * A value is optionally required from current editor's parser host
     * @template T
     * @param string|int $key Key to the value
     * @param Parser<T>|null $parser If supplied, parser to parse the value
     * @param T|null $default Default value to be returned when the value is not available
     * @return T|null
     * @throws ArgumentException
     */
    public function directOptional(string|int $key, ?Parser $parser = null, mixed $default = null) : mixed
    {
        if ($this->isKeyHidden($key)) return $default;
        if (!$this->parserHost->has($key)) return $default;

        $ret = $this->parserHost->optional($key, $parser, $default);
        $this->isChanged = true;
        return $ret;
    }


    /**
     * If given key is hidden
     * @param string|int $key
     * @return bool
     */
    protected final function isKeyHidden(string|int $key) : bool
    {
        return array_key_exists("$key", $this->hiddenKeys);
    }


    /**
     * If anything changed (edited)
     * @return bool
     */
    public final function isChanged() : bool
    {
        return $this->isChanged;
    }
}