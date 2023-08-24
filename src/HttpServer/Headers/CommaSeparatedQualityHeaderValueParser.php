<?php

namespace Magpie\HttpServer\Headers;

use Magpie\Codecs\Parsers\CreatableParser;
use Magpie\Codecs\Traits\CommonParser;
use Magpie\General\Concepts\ArraySortable;

/**
 * Parser for CommaSeparatedQualityHeaderValue
 * @extends CreatableParser<CommaSeparatedQualityHeaderValue>
 */
abstract class CommaSeparatedQualityHeaderValueParser extends CreatableParser
{
    use CommonParser;

    /**
     * @var ArraySortable<ColonSeparatedHeaderValue>|null Specific sorter to be used
     */
    protected ?ArraySortable $sorter = null;


    /**
     * Specify sorter to be used
     * @param ArraySortable|null $sorter
     * @return $this
     */
    public function withSorter(?ArraySortable $sorter) : static
    {
        $this->sorter = $sorter;
        return $this;
    }
}