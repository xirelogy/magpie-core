<?php

namespace Magpie\Codecs\Parsers;

use Carbon\CarbonInterface;
use Magpie\Codecs\ParserHosts\ArrayParserHost;
use Magpie\Exceptions\ParseFailedException;
use Magpie\General\Str;
use Magpie\Objects\SimpleTimeRange;

/**
 * A time range parser where range specified as UNIX timestamps
 * @extends CreatableParser<SimpleTimeRange>
 */
class SimpleTimeRangeFromTimestampParser extends CreatableParser
{
    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : SimpleTimeRange
    {
        /** @var string|null $value */
        $value = StringParser::createTrimEmptyAsNull()->parse($value, $hintName);
        if ($value === null) throw new ParseFailedException(_l('Nothing specified'));

        $components = explode('-', $value);
        $componentsCount = count($components);
        if ($componentsCount < 2) throw new ParseFailedException(_l('Separator not found'));
        if ($componentsCount > 2) throw new ParseFailedException(_l('Invalid format'));

        $parserHost = static::createParserHost($components, $hintName);
        $startAt = $parserHost->optional('startAt', static::createOptionalTimestampParser());
        $endAt = $parserHost->optional('endAt', static::createOptionalTimestampParser());

        $ret = new SimpleTimeRange($startAt, $endAt);
        if (!$ret->isValid()) throw new ParseFailedException(_l('Invalid value'));

        return $ret;
    }


    /**
     * Create specific parser host
     * @param array $components
     * @param string|null $hintName
     * @return ArrayParserHost
     */
    protected static function createParserHost(array $components, ?string $hintName) : ArrayParserHost
    {
        return new class($components, $hintName) extends ArrayParserHost {
            /**
             * @inheritDoc
             */
            protected function acceptKey(int|string $key) : string|int
            {
                return match ($key) {
                    'startAt' => 0,
                    'endAt' => 1,
                    default => parent::acceptKey($key),
                };
            }


            /**
             * @inheritDoc
             */
            protected function formatKey(int|string $key) : string|int
            {
                return match ($key) {
                    0 => 'startAt',
                    1 => 'endAt',
                    default => parent::formatKey($key),
                };
            }
        };
    }


    /**
     * Create the corresponding timestamp parser
     * @return Parser<CarbonInterface|null>
     */
    protected static function createOptionalTimestampParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : ?CarbonInterface {
            if (Str::isNullOrEmpty($value)) return null;
            return TimestampParser::create()->parse($value, $hintName);
        });
    }
}