<?php

namespace Magpie\System\Traits;

use Exception;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Boot only when necessary
 */
trait LazyBootable
{
    /**
     * Ensure that the factory is booted up
     * @return void
     */
    protected final static function ensureBoot() : void
    {
        if (static::$isBoot) return;
        static::$isBoot = true;

        try {
            static::onBoot();
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Boot up
     * @return void
     * @throws Exception
     */
    protected abstract static function onBoot() : void;
}