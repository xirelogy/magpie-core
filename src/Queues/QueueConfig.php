<?php

namespace Magpie\Queues;

use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Queue configuration
 */
abstract class QueueConfig implements Configurable, EnvConfigurable, TypeClassable
{
    use CommonConfigurable;
    use CommonTypeConfigurable;
    use EnvTypeConfigurable;


    /**
     * @inheritDoc
     */
    public static function fromEnv(?string ...$prefixes) : static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['QUEUE'], $prefixes));
        return static::fromConfig($provider, $selection);
    }
}