<?php

namespace Magpie\Queues\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\QueueFailHandler;

#[CommandSignature('queue:flush-failed')]
#[CommandDescriptionL('Flush all failed jobs')]
class FlushFailedCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        QueueFailHandler::getCurrent()->forget(null);

        Console::info(_l('Failed jobs emptied'));
    }
}