<?php

namespace Magpie\Queues\Concepts;

use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * May generate unique identity for queue items
 */
interface QueueIdentityProvidable extends DefaultProviderRegistrable
{
    /**
     * Generate a unique identity
     * @return string|int
     */
    public function generateId() : string|int;
}