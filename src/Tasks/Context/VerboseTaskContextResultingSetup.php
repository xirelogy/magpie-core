<?php

namespace Magpie\Tasks\Context;

use Magpie\Facades\Log;
use Magpie\General\Traits\StaticCreatable;
use Magpie\Tasks\FailedTaskResult;
use Magpie\Tasks\SuccessTaskResult;
use Magpie\Tasks\Task;
use Throwable;

class VerboseTaskContextResultingSetup extends TaskContextResultingSetup
{
    use StaticCreatable;


    public function onRunTaskSuccess(Task $task, mixed $result) : ?SuccessTaskResult
    {
        Log::info(_l('Task execution complete'), [
            'result' => $result,
        ]);

        return null;
    }


    /**
     * @inheritDoc
     */
    public function onRunTaskFailed(Task $task, Throwable $ex) : ?FailedTaskResult
    {
        Log::error(_format_l('Unexpected exception', 'Unexpected exception: {{0}}', $ex->getMessage()));
        Log::warning(_l('Exception trace'), [
            'trace' => $ex->getTrace(),
        ]);

        return null;
    }
}