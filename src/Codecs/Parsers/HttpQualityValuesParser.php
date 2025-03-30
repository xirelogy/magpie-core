<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\HttpQualityValue;
use Magpie\General\Str;

/**
 * Parse comma separated values with expressed q-values/q-factors
 * @extends CreatableParser<HttpQualityValue>
 */
class HttpQualityValuesParser extends CreatableParser
{
    /**
     * @inheritDoc
     * @return array<HttpQualityValue>|null
     */
    protected function onParse(mixed $value, ?string $hintName) : ?array
    {
        if ($value === null) return null;
        $value = StringParser::create()->parse($value, $hintName);
        if (Str::isNullOrEmpty($value)) return null;

        return iter_flatten(static::getSortedValues($value), false);
    }


    /**
     * Process sorted values
     * @param string $value
     * @return iterable<HttpQualityValue>
     * @throws SafetyCommonException
     */
    protected static function getSortedValues(string $value) : iterable
    {
        // Sort into weighted list
        /** @var array<int, array<string>> $weightedValues */
        $weightedValues = [];

        $specs = explode(',', $value);
        foreach ($specs as $spec) {
            $parsedSpec = static::parseSpec($spec);

            // @link https://developer.mozilla.org/en-US/docs/Glossary/Quality_values
            // The importance of a value is marked by the suffix ';q=' immediately followed by a value
            // between 0 and 1 included, with up to three decimal digits, the highest value denoting
            // the highest priority. When not present, the default value is 1
            $actualWeight = intval(round( $parsedSpec->q * 1000));

            $values = $weightedValues[$actualWeight] ?? [];
            $values[] = $parsedSpec->value;
            $weightedValues[$actualWeight] = $values;
        }

        // Sort and return
        krsort($weightedValues, SORT_NUMERIC);
        foreach ($weightedValues as $weight => $values) {
            foreach ($values as $value) {
                yield new HttpQualityValue($value, $weight / 1000);
            }
        }
    }


    /**
     * Parse value + optional q-values/q-factors
     * @param string $valueSpec
     * @return HttpQualityValue
     * @throws SafetyCommonException
     */
    protected static function parseSpec(string $valueSpec) : HttpQualityValue
    {
        $semiSections = explode(';', $valueSpec);
        if (count($semiSections) === 1) {
            return new HttpQualityValue(trim($semiSections[0]), 1.0);
        }

        // Expect format: q = <float_val>
        $equalSections = explode('=', $semiSections[1]);
        if (count($equalSections) < 2) throw new InvalidDataException();
        if (trim($equalSections[0]) !== 'q') throw new InvalidDataException();

        $q = FloatParser::create()->withMin(0)->withMax(1)->parse($equalSections[1]);
        return new HttpQualityValue(trim($semiSections[0]), $q);
    }
}