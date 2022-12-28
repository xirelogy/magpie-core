<?php

namespace Magpie\Queues;

use Magpie\Queues\Concepts\QueueFailHandleable;
use Magpie\Queues\Providers\Handlers\DefaultQueueFailHandler;
use Magpie\System\Concepts\DefaultProviderRegistrable;
use Magpie\System\Kernel\Kernel;

/**
 * Handler for failed queue item
 */
abstract class QueueFailHandler implements QueueFailHandleable, DefaultProviderRegistrable
{
    /**
     * @inheritDoc
     */
    public final function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(QueueFailHandleable::class, $this);
    }


    /**
     * Get current registered handle
     * @return self
     */
    public static final function getCurrent() : self
    {
        $handle = Kernel::current()->getProvider(QueueFailHandleable::class);
        if ($handle instanceof self) return $handle;

        return new DefaultQueueFailHandler();
    }
}