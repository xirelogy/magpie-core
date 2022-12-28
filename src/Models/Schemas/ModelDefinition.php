<?php

namespace Magpie\Models\Schemas;

use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\InvalidDataFormatValueException;
use Magpie\Exceptions\InvalidDataValueException;
use Magpie\General\Sugars\Quote;
use Stringable;

/**
 * Model definition as used in schema
 */
final class ModelDefinition implements Stringable
{
    /**
     * @var string The base type
     */
    public readonly string $baseType;
    /**
     * @var array<string|int> Detailed specifications
     */
    public readonly array $specs;


    /**
     * Constructor
     * @param string $baseType
     * @param array<string|int> $specs
     * @throws InvalidDataException
     */
    public function __construct(string $baseType, array $specs = [])
    {
        $this->baseType = strtolower(static::checkBaseType($baseType));
        $this->specs = $specs;
    }


    /**
     * Clone with alternative base type
     * @param string $newBaseType
     * @return static
     * @throws InvalidDataException
     */
    public function cloneWithBaseType(string $newBaseType) : static
    {
        return new static($newBaseType, $this->specs);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        if (count($this->specs) <= 0) return $this->baseType;

        return $this->baseType . Quote::bracket(implode(',', $this->specs));
    }


    /**
     * Parse from definition text
     * @param string $value
     * @return static
     * @throws InvalidDataException
     */
    public static function parse(string $value) : static
    {
        $openPos = strpos($value, '(');
        $closePos = strpos($value, ')');

        if ($openPos === false && $closePos === false) return new static($value);
        if ($openPos === false) throw new InvalidDataFormatValueException($value, _l('bracket not opened'));
        if ($closePos === false) throw new InvalidDataFormatValueException($value, _l('bracket not closed'));
        if ($closePos < $openPos) throw new InvalidDataFormatValueException($value, _l('bracket closed before open'));

        $baseType = substr($value, 0, $openPos);
        $specsText = substr($value, $openPos + 1, $closePos - $openPos - 1);
        $specs = explode(',', $specsText);

        $outSpecs = [];
        foreach ($specs as $spec) {
            $spec = trim($spec);
            if (is_numeric($spec)) $spec = intval($spec);

            $outSpecs[] = $spec;
        }

        return new static($baseType, $outSpecs);
    }


    /**
     * Check and ensure base type is valid
     * @param string $baseType
     * @return string
     * @throws InvalidDataException
     */
    protected static function checkBaseType(string $baseType) : string
    {
        if (!preg_match('/^[\w-]+$/', $baseType)) throw new InvalidDataValueException($baseType, _l('model base type'));
        return $baseType;
    }
}