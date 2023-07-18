<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\Providers\QueueCreator;
use Magpie\Queues\QueueFailHandler;

#[CommandSignature('queue:retry-failed {id}')]
#[CommandDescriptionL('Reset a failed job and put it back on queue')]
class RetryFailedCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $handler = QueueFailHandler::getCurrent();

        $id = $request->arguments->requires('id', StringParser::create());
        $failed = $handler->find($id);
        if ($failed === null) {
            Console::error(_format(_l('Failed job with ID = {{0}} not found'), $id));
            return;
        }

        $queue = QueueCreator::instance()->getQueue($failed->queue);
        $queue->enqueueFailed($failed);

        $handler->forget($id);

        Console::info(_format(_l('Job with ID = {{0}} placed back on queue'), $id));
    }
}