<?php

namespace Magpie\Models\Filters;

use Magpie\Models\Concepts\QueryFilterApplicable;
use Magpie\Models\QueryFilterService;

/**
 * Filter for a given fragment in query
 */
class FragmentFilter implements QueryFilterApplicable
{
    /**
     * @var int|null Size of the fragment (maximum number of items to return)
     */
    public readonly ?int $size;
    /**
     * @var int|null Offset from beginning before getting items
     */
    public readonly ?int $offset;


    /**
     * Constructor
     * @param int|null $size
     * @param int|null $offset
     */
    protected function __construct(?int $size, ?int $offset)
    {
        $this->size = $size;
        $this->offset = $offset;
    }


    /**
     * @inheritDoc
     */
    public function apply(QueryFilterService $service) : void
    {
        if ($this->size !== null) {
            $service->setLimit($this->size);
        }

        if ($this->offset !== null) {
            $service->setOffset($this->offset);
        }
    }


    /**
     * Create a new fragment filter mainly to limit by size
     * @param int $size Size of the fragment (maximum number of items to return)
     * @param int|null $offset Offset from beginning before getting items
     * @return static
     */
    public static function limit(int $size, ?int $offset = null) : static
    {
        return new static($size, $offset);
    }


    /**
     * Create a new fragment filter to offset from beginning before getting items
     * @param int $offset Offset from beginning before getting items
     * @return static
     */
    public static function offset(int $offset) : static
    {
        return new static(null, $offset);
    }
}