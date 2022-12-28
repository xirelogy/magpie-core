<?php

namespace Magpie\Models\Concepts;

use Exception;

/**
 * Receiver for transaction completion notification
 */
interface TransactionCompletedListenable
{
    /**
     * Get notified on transaction completion
     * @param bool $isCommitted
     * @return void
     * @throws Exception
     */
    public function notifyCompleted(bool $isCommitted) : void;
}