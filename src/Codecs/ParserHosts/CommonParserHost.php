<?php

namespace Magpie\Codecs\ParserHosts;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\MissingArgumentException;

/**
 * Common parser host
 */
abstract class CommonParserHost implements ParserHost
{
    /**
     * @var string|null Prefix in current parser host
     */
    protected ?string $prefix;
    /**
     * @var bool If empty strings are treated as null
     */
    protected bool $isEmptyStringAsNull = false;


    /**
     * Constructor
     * @param string|null $prefix
     */
    protected function __construct(?string $prefix)
    {
        $this->prefix = $prefix !== '' ? $prefix : null;
    }


    /**
     * Whether empty strings are treated as null
     * @param bool $isEmptyStringAsNull
     * @return $this
     */
    public function withEmptyStringAsNull(bool $isEmptyStringAsNull = true) : static
    {
        $this->isEmptyStringAsNull = $isEmptyStringAsNull;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function has(string|int $key) : bool
    {
        $inKey = $this->acceptKey($key);

        return $this->hasInternal($inKey);
    }


    /**
     * If current parser host has given key
     * @param string|int $inKey
     * @return bool
     */
    protected abstract function hasInternal(string|int $inKey) : bool;


    /**
     * @inheritDoc
     */
    public function requires(string|int $key, ?Parser $parser = null) : mixed
    {
        $inKey = $this->acceptKey($key);

        return $this->obtain($inKey, true, $parser, null);
    }


    /**
     * @inheritDoc
     */
    public function optional(int|string $key, ?Parser $parser = null, mixed $default = null) : mixed
    {
        $inKey = $this->acceptKey($key);

        return $this->obtain($inKey, false, $parser, $default);
    }


    /**
     * Obtain and process value for given internal key
     * @template T
     * @param int|string $inKey
     * @param bool $isMandatory
     * @param Parser|null $parser
     * @param mixed $default
     * @return mixed
     * @throws ArgumentException
     */
    protected function obtain(int|string $inKey, bool $isMandatory, ?Parser $parser, mixed $default) : mixed
    {
        $key = $this->formatKey($inKey);

        try {
            $raw = $this->obtainRaw($key, $inKey, $isMandatory, $default);
            $ret = $this->preprocessRaw($raw);

            // Process empty string as null if required
            if ($this->isEmptyStringAsNull && is_string($ret) && trim($ret) === '') {
                $ret = null;
            }

            // Null are treated differently
            if ($ret === null) {
                // Additional mandatory check
                if ($isMandatory) throw new MissingArgumentException($this->fullKey($key));

                // Always return here (null are not parsed)
                return null;
            }

            if ($parser !== null) {
                $ret = $parser->parse($ret, $this->fullKey($key));

                // Additional mandatory check
                if ($ret === null && $isMandatory) throw new MissingArgumentException($this->fullKey($key));
            }

            return $ret;
        } catch (MissingArgumentException $ex) {
            // Additional check because some 'MissingArgumentException' may leak from other sources
            if (!$isMandatory && static::currentKeyOf($ex->argName ?? '') === "$key") return $default;
            throw $ex;
        }
    }


    /**
     * Obtain raw value for given internal key
     * @param int|string $key
     * @param int|string $inKey
     * @param bool $isMandatory
     * @param mixed $default
     * @return mixed
     * @throws ArgumentException
     */
    protected abstract function obtainRaw(int|string $key, int|string $inKey, bool $isMandatory, mixed $default) : mixed;


    /**
     * Preprocess raw value, if required
     * @param mixed $raw
     * @return mixed
     * @throws ArgumentException
     */
    protected function preprocessRaw(mixed $raw) : mixed
    {
        _throwable() ?? throw new MissingArgumentException();

        return $raw;
    }


    /**
     * Accept key and reformat key to match internal format
     * @param string|int $key
     * @return string|int
     */
    protected function acceptKey(string|int $key) : string|int
    {
        return $key;
    }


    /**
     * Format key to match external (or display) format
     * @param string|int $key
     * @return string|int
     */
    protected function formatKey(string|int $key) : string|int
    {
        return $key;
    }


    /**
     * Extract the current key without the prefixes
     * @param string $fullKey
     * @return string
     */
    protected static function currentKeyOf(string $fullKey) : string
    {
        $lastDotPos = strrpos($fullKey, '.');
        $lastBracketPos = strrpos($fullKey, ']');

        // Full key is the current key
        if ($lastDotPos === false && $lastBracketPos === false) return $fullKey;

        // Other-wise full key is from the valid one
        if ($lastBracketPos === false) return substr($fullKey, $lastDotPos + 1);
        if ($lastDotPos === false) return substr($fullKey, $lastBracketPos + 1);

        // Choose the more relevant one
        $maxPos = max($lastBracketPos, $lastDotPos);
        return substr($fullKey, $maxPos + 1);
    }
}