<?php

namespace Magpie\Queues\Commands;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\Impls\Caches\WorkersRestartedAt;

#[CommandSignature('queue:restart-workers')]
#[CommandDescription('Restart all running queue workers')]
class RestartWorkersCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        WorkersRestartedAt::create();

        Console::info(_l('Restart signal sent to queue workers'));
    }
}