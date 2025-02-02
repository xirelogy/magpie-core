<?php

namespace Magpie\Configurations;

use Magpie\Configurations\Constants\ConfigNamingConvention;
use Magpie\General\Str;

/**
 * Configuration name (may expressed as name)
 */
class ConfigName
{
    /**
     * @var array<string> String segments
     */
    protected array $segments;
    /**
     * @var array<string, string> Specific value for given naming convention
     */
    protected array $specificNames = [];


    /**
     * Constructor
     * @param iterable<string> $segments
     */
    protected function __construct(iterable $segments)
    {
        $this->segments = iter_flatten($segments, false);
    }


    /**
     * Specify the specific value for given naming convention
     * @param ConfigNamingConvention $convention
     * @param string $value
     * @return $this
     */
    public function withSpecific(ConfigNamingConvention $convention, string $value) : static
    {
        $this->specificNames[$convention->value] = $value;
        return $this;
    }


    /**
     * Express as key
     * @return string
     */
    public function getKey() : string
    {
        return $this->format(ConfigNamingConvention::LOWER_KEBAB);
    }


    /**
     * Segments (in lowercase)
     * @return iterable<string>
     */
    public function getLowerSegments() : iterable
    {
        foreach ($this->segments as $segment) {
            yield strtolower($segment);
        }
    }


    /**
     * Segments (in uppercase)
     * @return iterable<string>
     */
    public function getUpperSegments() : iterable
    {
        foreach ($this->segments as $segment) {
            yield strtoupper($segment);
        }
    }


    /**
     * Format in given convention
     * @param ConfigNamingConvention $convention
     * @return string
     */
    public function format(ConfigNamingConvention $convention) : string
    {
        // Return specific name if available
        if (array_key_exists($convention->value, $this->specificNames)) {
            return $this->specificNames[$convention->value];
        }

        return match ($convention) {
            ConfigNamingConvention::LOWER_KEBAB
                => $this->caseFormat(fn(string $v) => strtolower($v), '-'),
            ConfigNamingConvention::LOWER_SNAKE
                => $this->caseFormat(fn(string $v) => strtolower($v), '_'),
            ConfigNamingConvention::UPPER_KEBAB
                => $this->caseFormat(fn(string $v) => strtoupper($v), '-'),
            ConfigNamingConvention::UPPER_SNAKE
                => $this->caseFormat(fn(string $v) => strtoupper($v), '_'),
            ConfigNamingConvention::CAMEL_CASE
                => $this->camelFormat(),
            ConfigNamingConvention::PASCAL_CASE
                => $this->pascalFormat(),
            default
                => '', // Unsupported
        };
    }


    /**
     * Fixed case formatting
     * @param callable(string):string $fn
     * @param string $separator
     * @return string
     */
    protected function caseFormat(callable $fn, string $separator) : string
    {
        $retSegments = iter_flatten(iter_filter($this->segments, $fn), false);
        return implode($separator, $retSegments);
    }


    /**
     * Camel formatting
     * @return string
     */
    protected function camelFormat() : string
    {
        $ret = '';

        $isFirst = true;
        foreach ($this->segments as $segment) {
            $segment = strtolower($segment);
            if ($isFirst) {
                $isFirst = false;
            } else {
                $segment = ucfirst($segment);
            }

            $ret .= $segment;
        }

        return $ret;
    }


    /**
     * Pascal formatting
     * @return string
     */
    protected function pascalFormat() : string
    {
        $ret = '';

        foreach ($this->segments as $segment) {
            $segment = strtolower($segment);
            $segment = ucfirst($segment);
            $ret .= $segment;
        }

        return $ret;
    }


    /**
     * Create an instance
     * @param iterable<string> $segments
     * @return static
     */
    public static function create(iterable $segments) : static
    {
        return new static($segments);
    }


    /**
     * Parse from given values: support single string, kebab/snake cases, space separated
     * @param string $value
     * @return static
     */
    public static function parse(string $value) : static
    {
        $value = str_replace(' ', '-', $value);
        $value = str_replace('_', '-', $value);

        $segments = explode('-', $value);
        $segments = iter_filter($segments, function (string $value) {
            if (Str::isNullOrEmpty($value)) return null;
            return $value;
        });

        return new static($segments);
    }
}