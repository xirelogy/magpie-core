<?php

namespace Magpie\General\Simples;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;
use Stringable;

/**
 * XML encoding/decoding, simplified to support commonly used flags
 */
class SimpleXML
{
    use StaticClass;

    /**
     * Default XML root name
     */
    public const DEFAULT_ROOT_NAME = 'xml';


    /**
     * Encode in XML format
     * @param mixed $value
     * @param string $rootElementName
     * @param int $options
     * @return string
     * @throws SafetyCommonException
     */
    public static function encode(mixed $value, string $rootElementName = self::DEFAULT_ROOT_NAME, int $options = 0) : string
    {
        _used($options);

        return static::encodeXmlValue($value, $rootElementName);
    }


    /**
     * Decode from XML format
     * @param string $encoded
     * @return mixed
     * @throws SafetyCommonException
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public static function decode(string $encoded) : mixed
    {
        $ret = simplexml_load_string($encoded, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($ret === false) throw new UnsupportedValueException($encoded, 'XML');
        return $ret;
    }


    /**
     * Encode value with its XML tag
     * @param mixed $value
     * @param string $elementName
     * @return string
     * @throws SafetyCommonException
     */
    protected static function encodeXmlValue(mixed $value, string $elementName) : string
    {
        return "<$elementName>" . static::encodeValue($value, $elementName) . "</$elementName>";
    }


    /**
     * Encode value
     * @param mixed $value
     * @param string $parentElementName
     * @return string
     * @throws SafetyCommonException
     */
    protected static function encodeValue(mixed $value, string $parentElementName) : string
    {
        if ($value === null) return '';

        if (is_numeric($value)) return "$value";

        if (is_string($value)) return static::encodeCDataValue($value);
        if ($value instanceof Stringable) return static::encodeCDataValue($value->__toString());

        if (is_object($value)) {
            $ret = '';
            foreach ($value as $objKey => $objValue) {
                $ret .= static::encodeXmlValue($objValue, $objKey);
            }
            return $ret;
        }

        if (is_iterable($value)) {
            $ret = '';
            foreach ($value as $subValue) {
                $ret .= static::encodeXmlValue($subValue, $parentElementName);
            }
            return $ret;
        }

        throw new UnsupportedException();
    }


    /**
     * Encode value with proper CDATA escape
     * @param string $value
     * @return string
     */
    protected static function encodeCDataValue(string $value) : string
    {
        $ret = '';
        $start = 0;

        // Escape ']]>' as necessary
        while (true) {
            $pos = strpos($value, ']]>', $start);
            if ($pos === false) break;

            $ret .= substr($value, $start, $pos - $start) . ']]]]><![CDATA[>';
            $start = $pos + 3;
        }

        $ret .= substr($value, $start);
        return "<![CDATA[$ret]]>";
    }
}