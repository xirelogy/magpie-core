<?php

namespace Magpie\Models;

use Exception;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\General\Sugars\Excepts;

/**
 * Representation of a unique identifier mostly used in database
 */
class Identifier implements PreferStringable
{
    /**
     * @var string|int Underlying value
     */
    protected string|int $baseValue;


    /**
     * Constructor
     * @param string|int $baseValue
     */
    protected function __construct(string|int $baseValue)
    {
        $this->baseValue = $baseValue;
    }


    /**
     * Display value
     * @return string
     * @throws Exception
     */
    public function getDisplay() : string
    {
        return static::raw2display($this->baseValue);
    }


    /**
     * Raw base value
     * @return string|int
     */
    public function getRaw() : string|int
    {
        return $this->baseValue;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return Excepts::noThrow(fn () => $this->getDisplay(), static::invalidString());
    }


    /**
     * Construct from display value
     * @param string $value
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromDisplay(string $value) : static
    {
        $baseValue = static::display2raw($value);
        return new static($baseValue);
    }


    /**
     * Construct from raw value
     * @param string|int $baseValue
     * @return static
     */
    public static function fromRaw(string|int $baseValue) : static
    {
        return new static($baseValue);
    }


    /**
     * Format the raw base value for display
     * @param string|int $baseValue
     * @return string
     * @throws SafetyCommonException
     */
    protected static function raw2display(string|int $baseValue) : string
    {
        _throwable() ?? throw new UnexpectedException();

        return "$baseValue";
    }


    /**
     * Parse the display value into base raw value
     * @param string $value
     * @return string|int
     * @throws SafetyCommonException
     */
    protected static function display2raw(string $value) : string|int
    {
        _throwable() ?? throw new UnexpectedException();

        return $value;
    }


    /**
     * Always expressed as string
     * @param Identifier|string|int $value
     * @return string
     */
    public static final function toString(self|string|int $value) : string
    {
        if ($value instanceof self) return Excepts::noThrow(fn() => $value->getDisplay(), static::invalidString());

        if (is_string($value)) return $value;
        return "$value";
    }


    /**
     * String representing invalid value
     * @return string
     */
    protected static function invalidString() : string
    {
        return '';
    }
}