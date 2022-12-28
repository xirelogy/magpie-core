<?php

namespace Magpie\System\Concepts;

use Magpie\Exceptions\OperationFailedException;

/**
 * May provide feature to manipulate system maintenance state
 */
interface SystemMaintainable extends DefaultProviderRegistrable
{
    /**
     * Set if system under maintenance
     * @param bool $isUnderMaintenance
     * @return void
     * @throws OperationFailedException
     */
    public function setMaintenanceMode(bool $isUnderMaintenance) : void;


    /**
     * Check if system is under maintenance
     * @return bool
     */
    public function isUnderMaintenance() : bool;
}