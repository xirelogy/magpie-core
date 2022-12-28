<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\UnsupportedException;
use Magpie\Models\Exceptions\ModelSafetyException;

/**
 * May directly perform transaction operation on database connection
 */
interface DirectTransactionable
{
    /**
     * Begin transaction
     * @return void
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function beginTransaction() : void;


    /**
     * Commit the transaction
     * @return void
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function commit() : void;


    /**
     * Roll back the transaction
     * @return void
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function rollback() : void;
}