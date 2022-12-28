<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\System\Concepts\ProcessOutputCollectable;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;
use Magpie\System\Process\ProcessStandardStream;

#[CommandSignature('queue:listen {--queue=}')]
#[CommandDescription('Run queue\'s listener')]
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

        $collector = new class implements ProcessOutputCollectable {
            /**
             * @inheritDoc
             */
            public function close() : void
            {
                // nop
            }


            /**
             * @inheritDoc
             */
            public function receive(ProcessStandardStream $stream, string $content) : void
            {
                $content = rtrim($content, "\r\n");

                Console::output($content);
            }


            /**
             * @inheritDoc
             */
            public function export(ProcessStandardStream $stream) : StreamReadable|StreamReadConvertible|iterable|string|null
            {
                return null;
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