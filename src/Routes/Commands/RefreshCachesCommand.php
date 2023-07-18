<?php

namespace Magpie\Routes\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Routes\RouteRegistry;

#[CommandSignature('route:refresh-caches')]
#[CommandDescriptionL('Refresh route caches')]
class RefreshCachesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        Console::info(_l('Refreshing route caches...'));
        RouteRegistry::saveSourceCache();

        Console::info(_l('Done'));
    }
}