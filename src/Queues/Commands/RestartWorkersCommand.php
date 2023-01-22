<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\Providers\QueueCreator;

#[CommandSignature('queue:restart-workers {--queue=}')]
#[CommandDescription('Restart all running queue workers')]
class RestartWorkersCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $queueName = $request->options->optional('queue', StringParser::create());

        $queue = QueueCreator::instance()->getQueue($queueName);
        $queue->signalWorkerRestart();

        Console::info(_l('Restart signal sent to queue workers'));
    }
}