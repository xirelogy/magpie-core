<?php

namespace Magpie\Queues\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\QueueFailHandler;

#[CommandSignature('queue:list-failed')]
#[CommandDescriptionL('List all failed jobs')]
class ListFailedCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $hasJob = false;
        foreach (QueueFailHandler::getCurrent()->listAll() as $job) {
            $hasJob = true;
            Console::info(_format(_l('Job: {{0}}, reason: [{{1}}] {{2}}'), $job->id, $job->exception->className, $job->exception->message));
        }

        if (!$hasJob) Console::info(_l('No failed jobs'));
    }
}