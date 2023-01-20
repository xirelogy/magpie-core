<?php

namespace Magpie\System\Concepts;

use Throwable;

interface AbnormalExitHandleable
{
    /**
     * Handle abnormal exit (right before the process gets terminated)
     * @param Throwable|null $ex
     * @param bool $isDebug
     * @return void
     */
    public function handleAbnormalExit(?Throwable $ex, bool $isDebug) : void;
}