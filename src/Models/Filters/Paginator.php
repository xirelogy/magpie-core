<?php

namespace Magpie\Models\Filters;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Models\Concepts\QueryFilterApplicable;

/**
 * Base paginator that can split query results into multiple pages
 */
abstract class Paginator implements Packable, QueryFilterApplicable
{
    use CommonPackable;

    /**
     * @var int Maximum number of items per page
     */
    public int $size;


    /**
     * Constructor
     * @param int $size Maximum number of items per page
     */
    protected function __construct(int $size)
    {
        $this->size = $size;
    }


    /**
     * Apply the paginator directly on given iterable
     * @param iterable $source
     * @return iterable
     * @throws SafetyCommonException
     */
    public abstract function applyOnIterable(iterable $source) : iterable;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {

    }
}