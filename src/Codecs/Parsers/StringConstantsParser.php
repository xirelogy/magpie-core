<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\UnsupportedValueException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * String constants (enumeration of constant values) parser
 * @extends CreatableParser<string>
 */
abstract class StringConstantsParser extends CreatableParser
{
    /**
     * @var array<string, string>|null Allowed constants
     */
    private ?array $constants = null;


    /**
     * @inheritDoc
     */
    protected final function onParse(mixed $value, ?string $hintName) : string
    {
        $this->ensureConstantsAvailable();

        $value = StringParser::create()->parse($value, $hintName);
        if (!array_key_exists($value, $this->constants)) throw new UnsupportedValueException($value);

        return $value;
    }


    /**
     * Ensure constants are available
     * @return void
     * @throws ReflectionException
     */
    private function ensureConstantsAvailable() : void
    {
        if ($this->constants !== null) return;

        $this->constants = [];
        foreach (static::getConstantClasses() as $className) {
            foreach (static::getConstantsFromClass($className) as $value => $name) {
                $this->constants[$value] = $name;
            }
        }
    }


    /**
     * Get all constants from given class
     * @param string $className
     * @return iterable<string, string>
     * @throws ReflectionException
     */
    private static function getConstantsFromClass(string $className) : iterable
    {
        $class = new ReflectionClass($className);
        foreach ($class->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $constant) {
            $name = $constant->getName();
            $value = $constant->getValue();
            if (!is_string($value)) continue;
            yield $value => $name;
        }
    }


    /**
     * Enumeration of all
     * @return iterable<class-string>
     */
    protected static abstract function getConstantClasses() : iterable;
}