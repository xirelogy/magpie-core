<?php

namespace Magpie\General\Concepts;

/**
 * May modify existing packed selectors
 */
interface PackSelectModifiable
{
    /**
     * Add selector to the selection
     * @param string|null ...$selectorSpecs Specification(s) of the selectors
     * @return $this
     */
    public function select(?string ...$selectorSpecs) : static;


    /**
     * Add selectors to the selection from another container
     * @param PackSelectEnumerable|null $container Container to be transferred from
     * @return $this
     */
    public function selectFrom(?PackSelectEnumerable $container) : static;


    /**
     * Propagate a selection to current container if it was selected in the given container
     * @param PackSelectConditionable $container Container to be checked for
     * @param string ...$selectors Selector(s) to be tested individually
     * @return $this
     */
    public function propagate(PackSelectConditionable $container, string ...$selectors) : static;
}