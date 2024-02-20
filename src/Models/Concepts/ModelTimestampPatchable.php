<?php

namespace Magpie\Models\Concepts;

use Carbon\CarbonInterface;

/**
 * May patch model timestamps
 */
interface ModelTimestampPatchable
{
    /**
     * Try to get creation timestamp
     * @param string $tableModelClass Table's model class name
     * @return CarbonInterface|null Corresponding creation timestamp if successful
     */
    public function tryCreateTimestamp(string $tableModelClass) : ?CarbonInterface;


    /**
     * Try to get update timestamp
     * @param string $tableModelClass Table's model class name
     * @return CarbonInterface|null Corresponding update timestamp if successful
     */
    public function tryUpdateTimestamp(string $tableModelClass) : ?CarbonInterface;
}