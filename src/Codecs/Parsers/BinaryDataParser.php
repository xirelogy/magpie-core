<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\ParseFailedException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\Objects\BinaryData;

/**
 * Binary data (from string) parser
 * @extends CreatableParser<BinaryData>
 */
class BinaryDataParser extends CreatableParser
{
    /**
     * @var bool If empty data is treated as null
     */
    protected bool $isEmptyAsNull = false;


    /**
     * With empty data treated as null
     * @param bool $isEmptyAsNull
     * @return $this
     */
    public function withEmptyAsNull(bool $isEmptyAsNull = true) : static
    {
        $this->isEmptyAsNull = $isEmptyAsNull;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : ?BinaryData
    {
        if ($value === null) return null;

        if ($value instanceof BinaryDataProvidable) $value = $value->getData();

        if ($this->isEmptyAsNull && $value === '') return null;

        if (is_string($value)) return BinaryData::fromBinary($value);

        throw new ParseFailedException(_l('Not a binary string'));
    }
}