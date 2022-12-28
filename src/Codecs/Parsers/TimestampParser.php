<?php

namespace Magpie\Codecs\Parsers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Codecs\Traits\CommonTimeImmutableParser;

/**
 * Timestamp (UNIX timestamp) parser
 * @extends CreatableParser<CarbonInterface>
 */
class TimestampParser extends CreatableParser
{
    use CommonTimeImmutableParser;


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : CarbonInterface
    {
        $value = IntegerParser::create()->parse($value, $hintName);

        $ret = Carbon::createFromTimestamp($value);
        if ($this->isImmutableOutput) $ret = $ret->toImmutable();
        return $ret;
    }
}