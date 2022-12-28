<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Http\HttpClientRequestBody;
use Magpie\General\Concepts\BinaryDataProvidable;
use Stringable;

/**
 * A body to be sent along with the request which is a form (`multipart/form-data`)
 */
class HttpFormClientRequestBody extends HttpClientRequestBody
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'form';

    /**
     * @var array<string, mixed> Key values
     */
    public array $keyValues;


    /**
     * Constructor
     * @param array<string, mixed> $keyValues
     */
    public function __construct(array $keyValues)
    {
        $this->keyValues = $keyValues;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Check key values to ensure that the values are of proper type
     * @param bool|null $isStringOnly
     * @return array<string, string|BinaryDataProvidable>
     * @throws UnsupportedValueException
     */
    public function checkKeyValues(?bool &$isStringOnly = null) : array
    {
        $ret = [];
        $isStringOnly = true;

        foreach ($this->keyValues as $key => $value) {
            $ret[$key] = $this->acceptValue($value, $isStringOnly);
        }

        return $ret;
    }


    /**
     * Accept and format values into allowable types
     * @param mixed $value
     * @param bool $isStringOnly
     * @return string|BinaryDataProvidable
     * @throws UnsupportedValueException
     */
    protected function acceptValue(mixed $value, bool &$isStringOnly) : string|BinaryDataProvidable
    {
        if ($value instanceof BinaryDataProvidable) {
            $isStringOnly = false;
            return $value;
        }

        if (is_string($value)) return $value;
        if (is_bool($value)) return $this->acceptBooleanValue($value);
        if (is_numeric($value)) return $value;

        if ($value instanceof Stringable) return $value->__toString();
        if (is_object($value) && method_exists($value, '__toString')) return $value->__toString();

        throw new UnsupportedValueException($value, _l('form value'));
    }


    /**
     * Accept and format boolean value
     * @param bool $value
     * @return string
     * @throws UnsupportedValueException
     */
    protected function acceptBooleanValue(bool $value) : string
    {
        _throwable() ?? throw new UnsupportedValueException($value);

        // Naive handling assuming booleans are representable by 0 and 1
        return $value ? '1' : '0';
    }
}