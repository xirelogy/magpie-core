<?php

namespace Magpie\Queues;

use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Queue configuration
 */
abstract class QueueConfig implements TypeClassable
{
    use EnvTypeConfigurable;


    /**
     * Create configuration from environment variables
     * @param string|null $prefix
     * @return static
     * @throws ArgumentException
     */
    public static function fromEnv(?string $prefix = null) : static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('QUEUE', $prefix);

        return static::fromEnvType($parserHost, $envKey);
    }
}