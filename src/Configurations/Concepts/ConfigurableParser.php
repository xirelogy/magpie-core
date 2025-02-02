<?php

namespace Magpie\Configurations\Concepts;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Indicate the specific parser can be reconfigured
 * @template T
 * @extends Parser<T>
 */
interface ConfigurableParser extends Parser
{
    /**
     * Parse given value using configuration
     * @param ConfigProvider $provider
     * @param ConfigSelectable|null $selection
     * @return T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public function parseConfig(ConfigProvider $provider, ?ConfigSelectable $selection) : mixed;
}