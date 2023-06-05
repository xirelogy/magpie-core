<?php

namespace Magpie\Cryptos\X509;

use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Excepts;

/**
 * X.509 names (combined from multiple name attributes)
 */
class Name implements PreferStringable
{
    /**
     * @var array<NameAttribute> List of attributes to form the name
     */
    protected array $attributes;


    /**
     * Constructor
     * @param iterable<NameAttribute> $attributes
     */
    protected function __construct(iterable $attributes)
    {
        $this->attributes = iter_flatten($attributes, false);
    }


    /**
     * All attributes
     * @return iterable<NameAttribute>
     */
    public function getAttributes() : iterable
    {
        yield from $this->attributes;
    }


    /**
     * All components (aka attributes)
     * @return iterable<string, string>
     * @deprecated
     */
    public function getComponents() : iterable
    {
        foreach ($this->attributes as $attribute) {
            yield $attribute->shortName => $attribute->value;
        }
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $ret = '';
        foreach ($this->attributes as $attribute) {
            $ret .= "/$attribute";
        }

        return $ret;
    }


    /**
     * Create from attributes
     * @param iterable<NameAttribute> $attributes
     * @return static
     */
    public static function fromAttributes(iterable $attributes) : static
    {
        return new static(iter_flatten($attributes));
    }


    /**
     * Create from map of components
     * @param iterable<string, string> $names
     * @return static
     * @deprecated
     */
    public static function fromComponents(iterable $names) : static
    {
        return static::fromAttributesMap($names);
    }


    /**
     * Create from attribute map
     * @param iterable $attributes
     * @return static
     */
    public static function fromAttributesMap(iterable $attributes) : static
    {
        $translate = function (iterable $attributes) {
            foreach ($attributes as $shortName => $value) {
                yield new NameAttribute($shortName, $value);
            }
        };

        return new static($translate($attributes));
    }


    /**
     * Create from text
     * @param string $text
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromText(string $text) : static
    {
        $attributes = [];
        $start = 0;

        for (;;) {
            if (substr($text, $start, 1) !== '/') throw new InvalidDataException();
            $nextSlash = strpos($text, '/', $start + 1);
            $attribute = $nextSlash !== false ? substr($text, $start + 1, $nextSlash - $start - 1) : substr($text, $start + 1);

            $equalPos = strpos($attribute, '=');
            if ($equalPos === false) throw new InvalidDataException();

            $attributeKey = substr($attribute, 0, $equalPos);
            $attributeValue = substr($attribute, $equalPos + 1);
            $attributes[] = new NameAttribute($attributeKey, $attributeValue);

            // Check for exit condition or continue
            if ($nextSlash === false) return static::fromAttributes($attributes);
            $start = $nextSlash;
        }
    }


    /**
     * Try creating from text
     * @param string $text
     * @return static|null
     */
    public static function tryFromText(string $text) : ?static
    {
        return Excepts::noThrow(fn () => static::fromText($text));
    }
}