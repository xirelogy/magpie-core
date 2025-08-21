<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\ParseFailedException;
use Magpie\Objects\BinaryData;

/**
 * Binary data (from BASE64 string) parser
 * @extends CreatableParser<BinaryData>
 */
class Base64BinaryDataParser extends CreatableParser
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
        $value = StringParser::createTrimEmptyAsNull()->parse($value, $hintName);
        if ($value === null) return null;

        if ($this->isEmptyAsNull && $value === '') return null;

        // URL-base64 compatibility and auto padding
        $value = str_replace('-', '+', $value);
        $value = str_replace('_', '/', $value);
        while ((strlen($value) % 4) != 0) {
            $value .= '=';
        }

        try {
            return BinaryData::fromBase64($value);
        } catch (\Throwable $ex) {
            throw new ParseFailedException(_l('Invalid base64 string'), previous: $ex);
        }
    }
}