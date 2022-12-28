<?php

namespace Magpie\Objects;

use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Binary data that can be expressed in various format
 */
class BinaryData implements PreferStringable
{
    /**
     * @var string Data in binary
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
     * Expressed as binary
     * @return string
     */
    public function asBinary() : string
    {
        return $this->binData;
    }


    /**
     * Expressed in hexadecimal (lowercase)
     * @return string
     */
    public function asLowerHex() : string
    {
        return strtolower(bin2hex($this->binData));
    }


    /**
     * Expressed in hexadecimal (uppercase)
     * @return string
     */
    public function asUpperHex() : string
    {
        return strtoupper(bin2hex($this->binData));
    }


    /**
     * Expressed in base64
     * @return string
     */
    public function asBase64() : string
    {
        return base64_encode($this->binData);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->asLowerHex();
    }


    /**
     * Construct from binary data
     * @param string $data
     * @return static
     */
    public static function fromBinary(string $data) : static
    {
        return new static($data);
    }


    /**
     * Construct from hexadecimal data
     * @param string $data
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromHex(string $data) : static
    {
        $binData = @hex2bin($data);
        if ($binData === false) throw new InvalidDataException();

        return new static($binData);
    }


    /**
     * Construct from base64 data
     * @param string $data
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromBase64(string $data) : static
    {
        $binData = @base64_decode($data);
        if ($binData === false) throw new InvalidDataException();

        return new static($binData);
    }
}