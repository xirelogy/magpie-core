<?php

namespace Magpie\Tasks\Context;

use Magpie\Facades\Log;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticCreatable;
use Magpie\Tasks\Task;

class VerboseTaskContextPreparingSetup extends TaskContextPreparingSetup
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    protected function onPrepareTask(Task $parentTask, TaskContext $parentContext) : void
    {
        Log::notice(_format_l('Preparing to run task', 'Preparing to run task: {{0}}', $parentTask->getName()));

        $desc = $parentTask->getDescription();
        if (!is_empty_string($desc)) Log::notice(_format_l($desc, 'Task description: {{0}}', $desc));

        $formattedInput = Excepts::noThrow(fn () => SimpleJSON::encode($parentTask->getInput()), 'err');
        Log::notice(_format_l('Task input', 'Task input: {{0}}', $formattedInput));
    }
}