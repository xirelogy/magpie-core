<?php

namespace Magpie\Models\Impls;

/**
 * Listener to listen for events relevant to query setup
 * @internal
 */
abstract class QuerySetupListener
{
    /**
     * Get notified on the updated attributes
     * @param array $attributes
     * @return void
     */
    public function notifyUpdateAttributes(array $attributes) : void
    {
        _used($attributes);
        // Default NOP
    }
}