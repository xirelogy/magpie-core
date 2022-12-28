<?php

namespace Magpie\Routes\Commands;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Routes\RouteRegistry;

#[CommandSignature('route:refresh-caches')]
#[CommandDescription('Refresh route caches')]
class RefreshCachesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        Console::info('Refreshing route caches...');
        RouteRegistry::saveSourceCache();

        Console::info('Done');
    }
}