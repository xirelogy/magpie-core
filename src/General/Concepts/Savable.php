<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Can be saved
 */
interface Savable
{
    /**
     * Save changes
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function save() : void;
}