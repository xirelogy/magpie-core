<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Queues\Providers\QueueCreator;

#[CommandSignature('queue:restart-workers {--queue=}')]
#[CommandDescriptionL('Restart all running queue workers')]
#[CommandOptionDescriptionL('queue', 'Target queue name')]
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