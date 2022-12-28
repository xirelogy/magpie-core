<?php

namespace Magpie\Queues\Concepts;

use Exception;
use Magpie\Queues\Simples\FailedExecutableEncoded;

/**
 * May handle a queue job that is severely failing (exhausted all attempts)
 */
interface QueueFailHandleable
{
    /**
     * Handle and record failed item
     * @param FailedExecutableEncoded $item
     * @return void
     * @throws Exception
     */
    public function handleFailed(FailedExecutableEncoded $item) : void;


    /**
     * List all failed items recorded
     * @return iterable<FailedExecutableEncoded>
     */
    public function listAll() : iterable;


    /**
     * Find item by given ID
     * @param string|int $id
     * @return FailedExecutableEncoded|null
     */
    public function find(string|int $id) : ?FailedExecutableEncoded;


    /**
     * Forget a particular item or all items
     * @param string|int|null $id ID of item to be forgotten, or null to forget all
     * @return void
     */
    public function forget(string|int|null $id) : void;
}