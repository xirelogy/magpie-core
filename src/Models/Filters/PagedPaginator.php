<?php

namespace Magpie\Models\Filters;

use Magpie\Exceptions\InvalidArgumentException;
use Magpie\General\Packs\PackContext;
use Magpie\Models\QueryFilterService;

/**
 * Paged base paginator
 */
class PagedPaginator extends Paginator
{
    /**
     * @var int Current page number (starting from page 1)
     */
    public int $page;
    /**
     * @var int|null Total number of items (as queried)
     */
    protected ?int $totalItems = null;


    /**
     * Constructor
     * @param int $page Current page number (starting from page 1)
     * @param int $size Maximum number of items per page
     */
    public function __construct(int $page, int $size)
    {
        parent::__construct($size);

        $this->page = $page;
    }


    /**
     * Current page
     * @return int
     */
    public function getCurrentPage() : int
    {
        return $this->page;
    }


    /**
     * Total number of items (as queried)
     * @return int|null
     */
    public function getTotalItems() : ?int
    {
        return $this->totalItems;
    }


    /**
     * Total number of pages
     * @return int|null
     */
    public function getTotalPages() : ?int
    {
        if ($this->totalItems === null) return null;
        if ($this->size < 1) return null;

        return floor(($this->totalItems + ($this->size - 1)) / $this->size);
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->currentPage = $this->getCurrentPage();
        $ret->totalItems = $this->getTotalItems();
        $ret->totalPages = $this->getTotalPages();
    }


    /**
     * @inheritDoc
     */
    public function applyOnIterable(iterable $source) : iterable
    {
        $count = 0;
        $totalSkip = ($this->page - 1) * ($this->size);

        $ret = [];
        foreach ($source as $item) {
            ++$count;
            if ($count <= $totalSkip) continue;

            if (($count - $totalSkip) <= $this->size) {
                $ret[] = $item;
            }
        }

        $this->totalItems = $count;

        yield from $ret;
    }


    /**
     * @inheritDoc
     */
    public function apply(QueryFilterService $service) : void
    {
        // Reject invalid values
        if ($this->page < 1) throw new InvalidArgumentException('paginator.page');
        if ($this->size < 1) throw new InvalidArgumentException('paginator.size');

        // Apply limit
        $service->setLimit($this->size);

        // Apply offset if applicable
        $offset = ($this->page - 1) * $this->size;
        if ($offset > 0) $service->setOffset($offset);

        // Query for total items
        $this->totalItems = $service->duplicateQuery()->count();
    }
}