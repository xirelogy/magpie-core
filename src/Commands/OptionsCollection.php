<?php

namespace Magpie\Commands;

/**
 * Options collection
 */
abstract class OptionsCollection extends Collection
{
    /**
     * Constructor
     * @param array $arr
     * @param string|null $prefix
     */
    public function __construct(array $arr, ?string $prefix = null)
    {
        parent::__construct($arr, $prefix);

        $this->argType = _l('option');
    }
}