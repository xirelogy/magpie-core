<?php

namespace Magpie\System\Concepts;

use Magpie\General\Concepts\TypeClassable;

/**
 * May be registered as default provider
 */
interface DefaultProviderRegistrable extends TypeClassable
{
    /**
     * Register as a default provider for its relevant services
     * @return void
     */
    public function registerAsDefaultProvider() : void;
}