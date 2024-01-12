<?php

namespace Magpie\Models\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Logs\Formats\CleanConsoleLogStringFormat;
use Magpie\Models\Commands\Features\DatabaseCommandFeature;

/**
 * Synchronize database schema to database
 */
#[CommandSignature('db:sync-schema')]
#[CommandDescriptionL('Synchronize database schema to database')]
class SyncSchemaCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $listener = DatabaseCommandFeature::createSyncSchemaListener(Console::asLogger(new CleanConsoleLogStringFormat()));
        DatabaseCommandFeature::syncSchema($listener);
    }
}