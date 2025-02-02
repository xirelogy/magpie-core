<?php

namespace Magpie\Configurations\Traits;

use Closure;
use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\Concepts\ConfigurableParser;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Exceptions\UnsupportedException;

/**
 * Trait to support reconfiguration parsing
 * @requires \Magpie\Configurations\Concepts\Configurable
 */
trait CommonConfigurableParser
{
    /**
     * Create a parser for reconfiguration
     * @return ConfigurableParser<static>
     */
    public static function createConfigParser() : ConfigurableParser
    {
        $fn = static::fromConfig(...);

        return new class($fn) implements ConfigurableParser {
            public function __construct(
                protected readonly Closure $fn,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function parse(mixed $value, ?string $hintName = null) : mixed
            {
                throw new UnsupportedException();
            }


            /**
             * @inheritDoc
             */
            public function parseConfig(ConfigProvider $provider, ?ConfigSelectable $selection) : mixed
            {
                return ($this->fn)($provider, $selection);
            }
        };
    }
}