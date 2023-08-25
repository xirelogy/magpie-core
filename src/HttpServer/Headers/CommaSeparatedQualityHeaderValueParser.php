<?php

namespace Magpie\HttpServer\Headers;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Traits\CommonParser;
use Magpie\General\Concepts\ArraySortable;

/**
 * Parser for CommaSeparatedQualityHeaderValue
 * @implements Parser<CommaSeparatedQualityHeaderValue>
 */
abstract class CommaSeparatedQualityHeaderValueParser implements Parser
{
    use CommonParser;

    /**
     * @var ArraySortable<ColonSeparatedHeaderValue>|null Specific sorter to be used
     */
    protected ?ArraySortable $sorter = null;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


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