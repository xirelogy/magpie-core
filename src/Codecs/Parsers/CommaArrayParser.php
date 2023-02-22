<?php

namespace Magpie\Codecs\Parsers;

use Magpie\General\Str;

/**
 * Array parser from comma separated string
 */
class CommaArrayParser extends ArrayParser
{
    /**
     * @var string Separator (comma)
     */
    protected string $separator = ',';


    /**
     * Specify the separator
     * @param string $separator
     * @return $this
     */
    public function withSeparator(string $separator) : static
    {
        $this->separator = $separator;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParseArray(mixed $value, ?string $hintName) : array
    {
        $originalValue = StringParser::create()->withEmptyAsNull()->parse($value, $hintName);
        if ($originalValue === null) return [];
        $values = explode($this->separator, $originalValue);

        $outValues = [];
        foreach ($values as $value) {
            $outValue = trim($value);
            if (Str::isNullOrEmpty($outValue)) continue;
            $outValues[] = $outValue;
        }

        return parent::onParseArray($outValues, $hintName);
    }
}