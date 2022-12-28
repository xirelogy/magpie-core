<?php

namespace Magpie\Routes\Commands;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Routes\RouteRegistry;

#[CommandSignature('route:delete-caches')]
#[CommandDescription('Delete route caches')]
class DeleteCachesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        Console::info('Deleting route caches...');
        RouteRegistry::deleteSourceCache();

        Console::info('Done');
    }
}