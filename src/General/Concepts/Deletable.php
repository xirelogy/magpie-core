<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Anything deletable
 */
interface Deletable
{
    /**
     * Delete the current item
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function delete() : void;
}