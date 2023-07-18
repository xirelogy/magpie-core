<?php

namespace Magpie\Schedules\Commands;

use Carbon\Carbon;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Schedules\Impls\ScheduleRegistry;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\MainLoop;

/**
 * Run scheduled job
 */
#[CommandSignature('schedule:run')]
#[CommandDescriptionL('Run scheduled job')]
class RunCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $now = Carbon::now();
        $entries = ScheduleRegistry::getEntries();

        $backgroundProcesses = [];
        $foregroundProcesses = [];

        foreach ($entries as $entry) {
            if (!$entry->isDue($now)) continue;

            $process = $entry->runner->_createProcess();

            if ($entry->isRunInBackground) {
                $backgroundProcesses[] = $process;
            } else {
                $foregroundProcesses[] = $process;
            }
        }

        $hasBackground = count($backgroundProcesses) > 0;

        if ($hasBackground) {
            foreach ($backgroundProcesses as $backgroundProcess) {
                EasyFiber::run(function () use ($backgroundProcess) {
                    $running = $backgroundProcess->runAsync();
                    $exitCode = $running->wait();
                    _used($exitCode);
                });
            }
        }

        // Run all foreground processes
        foreach ($foregroundProcesses as $foregroundProcess) {
            $foregroundProcess->run();
        }

        if ($hasBackground) {
            MainLoop::run();
        }
    }
}