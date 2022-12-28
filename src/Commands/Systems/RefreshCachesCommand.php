<?php

namespace Magpie\Commands\Systems;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\Kernel\Kernel;

/**
 * Refresh and maintain source cache
 */
#[CommandSignature('sys:refresh-caches')]
#[CommandDescription('Refresh and maintain source cache')]
class RefreshCachesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        Console::info(_l('Removing existing caches...'));
        foreach (Kernel::current()->getConfig()->getSourceCacheableClasses() as $class) {
            if (!is_subclass_of($class, SourceCacheable::class)) continue;
            $class::deleteSourceCache();
        }

        Console::info(_l('Updating caches...'));
        foreach (Kernel::current()->getConfig()->getSourceCacheableClasses() as $class) {
            if (!is_subclass_of($class, SourceCacheable::class)) continue;
            $class::saveSourceCache();
        }

        Console::info(_l('Done'));
    }
}