<?php

namespace Magpie\Cryptos;

use Magpie\Codecs\Concepts\PreferStringable;

/**
 * Numeric numbers that might be most probably large number, commonly expressed as hex strings
 */
class Numerals implements PreferStringable
{
    /**
     * @var string Underlying binary data
     */
    protected string $binData;


    /**
     * Constructor
     * @param string $binData
     */
    protected function __construct(string $binData)
    {
        $this->binData = $binData;
    }


    /**
     * Expressed as hex string
     * @return string
     */
    public function asHex() : string
    {
        return strtolower(bin2hex($this->binData));
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->asHex();
    }


    /**
     * Construct from binary data
     * @param string $binData
     * @return static
     */
    public static function fromBinary(string $binData) : static
    {
        return new static($binData);
    }


    /**
     * Construct from hex data
     * @param string $hexData
     * @return static|null
     */
    public static function fromHex(string $hexData) : ?static
    {
        $hexData = trim($hexData);
        if ($hexData === '') return null;

        $binData = @hex2bin($hexData);
        if ($binData === false) return null;

        return new static($binData);
    }


    /**
     * A zero numeral
     * @return static
     */
    public static function zero() : static
    {
        return new static(chr(0));
    }
}