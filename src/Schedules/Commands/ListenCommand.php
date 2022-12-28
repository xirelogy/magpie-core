<?php

namespace Magpie\Schedules\Commands;

use Carbon\Carbon;
use Exception;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\MainLoop;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;

/**
 * Run scheduler in foreground
 */
#[CommandSignature('schedule:listen')]
#[CommandDescription('Run scheduler in foreground')]
class ListenCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        static::startLoopFiber();

        MainLoop::run();
    }


    /**
     * Start a loop in its asynchronous fiber
     * @return void
     * @throws Exception
     */
    protected static function startLoopFiber() : void
    {
        EasyFiber::run(function () {
            $lastRunTimestamp = null;

            while (true) {
                EasyFiber::sleep(Duration::inMilliseconds(250));
                $now = Carbon::now();
                if ($now->second != 0) continue;

                $nowTimestamp = $now->getTimestamp();
                if ($nowTimestamp === $lastRunTimestamp) continue;

                $lastRunTimestamp = $nowTimestamp;

                Excepts::noThrow(fn () => static::startSchedulerFiber());
            }
        });
    }


    /**
     * Start a scheduler in its asynchronous fiber
     * @return void
     * @throws Exception
     */
    protected static function startSchedulerFiber() : void
    {
        EasyFiber::run(function () {
            Console::info(_l('Starting scheduler...'));
            $commandLine = ProcessCommandLine::fromCommand(RunCommand::getCommand());
            $process = Process::fromCommandLine($commandLine);
            $runner = $process->runAsync();

            $exitCode = $runner->wait();
            Console::info(_format_safe(_l('Scheduler exit with code {{0}}'), $exitCode) ?? _l('Scheduler exit'));
        });
    }
}