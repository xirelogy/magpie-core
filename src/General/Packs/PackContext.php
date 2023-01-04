<?php

namespace Magpie\General\Packs;

use Magpie\General\Concepts\PackSelectConditionable;
use Magpie\General\Concepts\PackSelectEnumerable;

/**
 * A context to pack object
 */
abstract class PackContext implements PackSelectConditionable
{
    /**
     * @var object Associated pack target
     */
    public readonly object $ret;
    /**
     * @var PackSelectEnumerable|null Globally selected selectors
     */
    public readonly ?PackSelectEnumerable $globalSelectors;
    /**
     * @var PackSelectors Currently effective selectors
     */
    protected readonly PackSelectors $effectiveSelectors;


    /**
     * Constructor
     * @param object $ret
     * @param PackSelectEnumerable|null $globalSelectors
     * @param string|null ...$localSelectorSpecs
     */
    protected function __construct(object $ret, ?PackSelectEnumerable $globalSelectors, ?string... $localSelectorSpecs)
    {
        $this->ret = $ret;
        $this->globalSelectors = $globalSelectors;

        $this->effectiveSelectors = new PackSelectors();
        $this->effectiveSelectors->selectFrom($globalSelectors);
        $this->effectiveSelectors->select(...$localSelectorSpecs);
    }


    /**
     * @inheritDoc
     */
    public function isAnySelected(string ...$selectors) : bool
    {
        return $this->effectiveSelectors->isAnySelected(...$selectors);
    }


    /**
     * @inheritDoc
     */
    public function isNoneSelected(string ...$selectors) : bool
    {
        return $this->effectiveSelectors->isNoneSelected(...$selectors);
    }


    /**
     * If any of the depth-limiting selectors selected
     * @param string ...$selectors
     * @return bool
     */
    public function isLimitDepth(string ...$selectors) : bool
    {
        return $this->isAnySelected(...$selectors);
    }


    /**
     * If none of the depth-reaching selectors selected
     * @param string ...$selectors
     * @return bool
     */
    public function isUnreachableDepth(string ...$selectors) : bool
    {
        return $this->isNoneSelected(...$selectors);
    }


    /**
     * Forward selection from current context
     * @return PackSelectEnumerable|null
     */
    public function forward() : ?PackSelectEnumerable
    {
        return $this->effectiveSelectors;
    }
}