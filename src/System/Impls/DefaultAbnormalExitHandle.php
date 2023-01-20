<?php

namespace Magpie\System\Impls;

use Magpie\System\Concepts\AbnormalExitHandleable;
use Throwable;

/**
 * Default abnormal exit handle
 * @internal
 */
class DefaultAbnormalExitHandle implements AbnormalExitHandleable
{
    /**
     * @inheritDoc
     */
    public function handleAbnormalExit(?Throwable $ex, bool $isDebug) : void
    {
        if ($isDebug) {
            // Show details of crash
            if ($ex) dd($ex);

            // Otherwise, try to show an error message
            $message = $ex?->getMessage() ?? 'Server error';
            echo "$message\n";
        } else {
            // Indicate that an abnormal error had occurred without crash details
            echo "Server error (abnormal)\n";
        }
    }
}