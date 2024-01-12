<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;
use Magpie\System\Process\ProcessStandardStream;
use Magpie\System\Process\SimpleReceivingProcessOutputCollector;

#[CommandSignature('queue:listen {--queue=}')]
#[CommandDescriptionL('Run queue\'s listener')]
#[CommandOptionDescriptionL('queue', 'Target queue name')]
class ListenCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $queueName = $request->options->optional('queue', StringParser::create());

        $commandArgs = [];
        $commandArgs[] = '--once';
        if ($queueName !== null) $commandArgs[] = "--queue=$queueName";

        $commandLine = ProcessCommandLine::fromCommand(RunWorkerCommand::getCommand(), ...$commandArgs);

        $collector = new class extends SimpleReceivingProcessOutputCollector {
            /**
             * @inheritDoc
             */
            public function receive(ProcessStandardStream $stream, string $content) : void
            {
                $content = rtrim($content, "\r\n");

                Console::output($content);
            }
        };

        while (true) {
            $process = Process::fromCommandLine($commandLine)
                ->withOutput($collector)
                ->withTimeout(null)
                ;

            $process->run();
        }
    }
}