<?php

namespace Magpie\Schedules\Commands;

use Carbon\Carbon;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Consoles\ConsoleTable;
use Magpie\Facades\Console;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\Schedules\Impls\ScheduleRegistry;

/**
 * List scheduled items
 */
#[CommandSignature('schedule:list')]
#[CommandDescriptionL('List scheduled job')]
class ListCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $now = Carbon::now();

        $table = new ConsoleTable([
            _l('Target'),
            _l('Cron expression'),
            _l('Next run'),
        ]);

        foreach (ScheduleRegistry::getEntries() as $entry) {
            $nextRun = $entry->getNextRunTime($now);

            $table->addRow([
                $entry->runner->getDesc(),
                $entry->cronExpression,
                $nextRun->setTimezone(SystemTimezone::default())->format('Y-m-d H:i:s'),
            ]);
        }

        Console::display($table);
    }
}