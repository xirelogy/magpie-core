<?php

namespace Magpie\Queues\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Logs\Formats\CleanConsoleLogStringFormat;
use Magpie\Queues\Providers\QueueCreator;

#[CommandSignature('queue:initialize')]
#[CommandDescriptionL('Initialize the queue system')]
class InitializeCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $logger = Console::asLogger(new CleanConsoleLogStringFormat());
        QueueCreator::instance()->initialize($logger);

        Console::info(_l('Queue system initialized'));
    }
}