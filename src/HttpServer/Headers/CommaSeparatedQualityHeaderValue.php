<?php

namespace Magpie\HttpServer\Headers;

use Closure;
use Exception;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\FloatParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Concepts\ArraySortable;
use Magpie\General\Str;
use Magpie\General\Sugars\Quote;

/**
 * Comma separated value with quality (weight) specifiable
 */
class CommaSeparatedQualityHeaderValue implements ObjectParseable
{
    /**
     * Precision (amplifying) level for the quality value
     */
    public const QUALITY_PRECISION = 1000;

    /**
     * @var array<QualityValue> Weighted values, from highest to lowest priority
     */
    public readonly array $values;


    /**
     * Constructor
     * @param iterable<QualityValue> $values
     */
    protected function __construct(iterable $values)
    {
        $this->values = iter_flatten($values, false);
    }


    /**
     * @inheritDoc
     * @return CommaSeparatedQualityHeaderValueParser
     */
    public static function createParser() : CommaSeparatedQualityHeaderValueParser
    {
        $fn = function (mixed $value, ?string $hintName, ?ArraySortable $sorter) : static {
            $value = StringParser::create()->parse($value, $hintName);
            $values = static::explodeValues($value, $hintName);
            $values = static::packValues($values, $sorter);
            return new static($values);
        };

        return new class($fn) extends CommaSeparatedQualityHeaderValueParser {
            /**
             * Constructor
             * @param Closure $fn
             */
            public function __construct(
                protected Closure $fn,
            ) {
                parent::__construct();
            }


            /**
             * @inheritDoc
             */
            protected function onParse(mixed $value, ?string $hintName) : CommaSeparatedQualityHeaderValue
            {
                return ($this->fn)($value, $hintName, $this->sorter);
            }
        };
    }


    /**
     * Explode the values and categorize according to weight
     * @param string $line
     * @param string|null $hintName
     * @return array
     * @throws Exception
     */
    protected static function explodeValues(string $line, ?string $hintName) : array
    {
        $ret = [];

        $segmentParser = ColonSeparatedHeaderValue::createParser();
        $qualityParser = ClosureParser::create(function (mixed $value, ?string $hintName) : int {
            $value = FloatParser::create()->withRange(0, 1)->parse($value, $hintName);
            return intval(floor($value * static::QUALITY_PRECISION));
        });

        $currentIndex = -1;
        foreach (explode(',', $line) as $segment) {
            ++$currentIndex;
            $segmentValue = $segmentParser->parse($segment, static::createSegmentParserPrefix($hintName, $currentIndex));
            $q = $segmentValue->optional('q', $qualityParser, QualityValue::DEFAULT_QUALITY);

            $qualityValues = $ret[$q] ?? [];
            $qualityValues[] = $segmentValue;
            $ret[$q] = $qualityValues;
        }

        krsort($ret);
        return $ret;
    }


    /**
     * Create a prefix for the underlying segment parser
     * @param string|null $hintName
     * @param int $index
     * @return string|null
     */
    private static function createSegmentParserPrefix(?string $hintName, int $index) : ?string
    {
        if (Str::isNullOrEmpty($hintName)) return null;
        return $hintName . Quote::square($index);
    }


    /**
     * Pack the values
     * @param array<int, array<ColonSeparatedHeaderValue>> $values
     * @param ArraySortable<ColonSeparatedHeaderValue>|null $sorter
     * @return iterable<QualityValue>
     * @throws Exception
     */
    protected static function packValues(array $values, ?ArraySortable $sorter) : iterable
    {
        foreach ($values as $weight => $subValues) {
            if ($sorter !== null) $subValues = $sorter->sort($subValues);
            foreach ($subValues as $subValue) {
                yield QualityValue::fromColonSeparatedHeaderValue($subValue, floatval($weight) / static::QUALITY_PRECISION);
            }
        }
    }
}