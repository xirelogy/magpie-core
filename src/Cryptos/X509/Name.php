<?php

namespace Magpie\Cryptos\X509;

use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Excepts;

/**
 * X.509 names (combined from multiple components)
 */
class Name implements PreferStringable
{
    /**
     * @var array<string, string> Names with component short name as keys
     */
    protected array $names;


    /**
     * Constructor
     * @param array<string, string> $names
     */
    protected function __construct(array $names)
    {
        $this->names = $names;
    }


    /**
     * All components (aka attributes)
     * @return iterable<string, string>
     */
    public function getComponents() : iterable
    {
        foreach ($this->names as $component => $value) {
            yield $component => $value;
        }
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $ret = '';
        foreach ($this->names as $component => $value) {
            $ret .= "/$component=$value";
        }

        return $ret;
    }


    /**
     * Create from map of components
     * @param iterable<string, string> $names
     * @return static
     */
    public static function fromComponents(iterable $names) : static
    {
        return new static(iter_flatten($names));
    }


    /**
     * Create from text
     * @param string $text
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromText(string $text) : static
    {
        $components = [];
        $start = 0;

        for (;;) {
            if (substr($text, $start, 1) !== '/') throw new InvalidDataException();
            $nextSlash = strpos($text, '/', $start + 1);
            $component = $nextSlash !== false ? substr($text, $start + 1, $nextSlash - $start - 1) : substr($text, $start + 1);

            $equalPos = strpos($component, '=');
            if ($equalPos === false) throw new InvalidDataException();

            $componentKey = substr($component, 0, $equalPos);
            $componentValue = substr($component, $equalPos + 1);
            $components[$componentKey] = $componentValue;

            // Check for exit condition or continue
            if ($nextSlash === false) return static::fromComponents($components);
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