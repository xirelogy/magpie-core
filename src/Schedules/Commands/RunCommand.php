<?php

namespace Magpie\Schedules\Commands;

use Carbon\Carbon;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Schedules\Impls\ScheduleRegistry;
use Magpie\System\Kernel\EasyFiberPromise;
use Magpie\System\Process\Process;

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

        $backgroundPromises = [];
        if ($hasBackground) {
            foreach ($backgroundProcesses as $backgroundProcess) {
                $backgroundPromises[] = EasyFiberPromise::create(function (Process $backgroundProcess) {
                    $running = $backgroundProcess->runAsync();
                    return $running->wait();
                }, $backgroundProcess);
            }
        }

        // Run all foreground processes
        foreach ($foregroundProcesses as $foregroundProcess) {
            $foregroundProcess->run();
        }

        // Wait until all background processes completes
        EasyFiberPromise::loop();
        _used($backgroundPromises);
    }
}