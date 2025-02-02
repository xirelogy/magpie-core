<?php

namespace Magpie\Configurations\Concepts;

use Magpie\Configurations\ConfigRedirect;
use Magpie\Configurations\Providers\ConfigProvider;

/**
 * May redirect configuration
 * @template T
 */
interface ConfigRedirectable
{
    /**
     * Create a setup to redirect configuration
     * @param ConfigProvider $provider
     * @return ConfigRedirect<T>
     */
    public static function createConfigRedirectSetup(ConfigProvider $provider) : ConfigRedirect;
}