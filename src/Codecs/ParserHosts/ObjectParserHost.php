<?php

namespace Magpie\Codecs\ParserHosts;

use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Parse host based on an object
 */
class ObjectParserHost extends CommonParserHost
{
    /**
     * @var object Host object
     */
    public readonly object $obj;


    /**
     * Constructor
     * @param object $obj
     * @param string|null $prefix
     */
    public function __construct(object $obj, ?string $prefix = null)
    {
        parent::__construct($prefix);

        $this->obj = $obj;
    }


    /**
     * @inheritDoc
     */
    protected function hasInternal(string|int $inKey) : bool
    {
        return property_exists($this->obj, $inKey);
    }


    /**
     * @inheritDoc
     */
    protected function obtainRaw(int|string $key, int|string $inKey, bool $isMandatory, mixed $default) : mixed
    {
        if (!property_exists($this->obj, $inKey)) {
            if ($isMandatory) throw new MissingArgumentException($this->fullKey($key), argType: $this->argType);
            return $default;
        }

        return $this->obj->{$inKey};
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        $prefix = !is_empty_string($this->prefix) ? ($this->prefix . '.') : '';
        return $prefix . $key;
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        if (is_empty_string($this->prefix)) return $key;
        if (is_empty_string($key)) return $this->prefix;

        return $this->prefix . '.' . $key;
    }


    /**
     * Accept the target
     * @param mixed $value
     * @param string|null $prefix
     * @return static
     * @throws SafetyCommonException
     */
    public static function accept(mixed $value, ?string $prefix = null) : static
    {
        if ($value instanceof static) return $value;
        if (!is_object($value)) throw new NotOfTypeException($value, _l('object'));
        return new static($value, $prefix);
    }
}