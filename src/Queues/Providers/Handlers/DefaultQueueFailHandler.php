<?php

namespace Magpie\Queues\Providers\Handlers;

use Magpie\Queues\QueueFailHandler;
use Magpie\Queues\Simples\FailedExecutableEncoded;

/**
 * Default (no operation) handle for failed queue item
 */
class DefaultQueueFailHandler extends QueueFailHandler
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'default';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function handleFailed(FailedExecutableEncoded $item) : void
    {
        // NOP
    }


    /**
     * @inheritDoc
     */
    public function listAll() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function find(int|string $id) : ?FailedExecutableEncoded
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function forget(int|string|null $id) : void
    {
        // NOP
    }
}