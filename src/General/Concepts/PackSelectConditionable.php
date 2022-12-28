<?php

namespace Magpie\General\Concepts;

/**
 * May check for pack selection conditions
 */
interface PackSelectConditionable
{
    /**
     * If any of the given selectors selected
     * @param string ...$selectors
     * @return bool
     */
    public function isAnySelected(string ...$selectors) : bool;


    /**
     * If none of the given selectors selected
     * @param string ...$selectors
     * @return bool
     */
    public function isNoneSelected(string ...$selectors) : bool;
}