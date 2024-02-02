<?php

namespace Magpie\System\Kernel;

use Magpie\Facades\Console;
use Magpie\System\Concepts\AbnormalExitHandleable;
use Throwable;

/**
 * Abnormal exit handle showing in console
 */
class ConsoleAbnormalExitHandle implements AbnormalExitHandleable
{
    /**
     * @inheritDoc
     */
    public function handleAbnormalExit(?Throwable $ex, bool $isDebug) : void
    {
        if ($ex === null || !$isDebug) {
            Console::error(_l('Unexpected abnormal error'));
            return;
        }

        $isFirst = true;
        for (;;) {
            if ($isFirst) {
                Console::error(_format_l('Unexpected abnormal error', 'Unexpected abnormal error: {{0}}', $ex->getMessage()));
                $isFirst = false;
            } else {
                Console::error(_format_l('- caused by error', '- caused by: {{0}}', $ex->getMessage()));
            }

            $traces = explode("\n", $ex->getTraceAsString());
            foreach ($traces as $trace) {
                Console::warning('   ' . $trace);
            }

            $ex = $ex->getPrevious();
            if ($ex === null) break;
        }
    }
}