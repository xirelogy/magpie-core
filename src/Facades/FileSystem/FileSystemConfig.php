<?php

namespace Magpie\Facades\FileSystem;

use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * File system configuration
 */
abstract class FileSystemConfig implements Configurable, EnvConfigurable, TypeClassable
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
        $selection = new EnvConfigSelection(array_merge(['STORE'], $prefixes));

        return static::fromConfig($provider, $selection);
    }
}