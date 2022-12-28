<?php

namespace Magpie\System\Concepts;

use Exception;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Bootable during startup
 */
interface SystemBootable
{
    /**
     * Register for system boot up
     * @param BootRegistrar $registrar Boot registrar
     * @return bool If current registration valid (shall be booted)
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool;


    /**
     * System boot-up
     * @param BootContext $context Boot up context
     * @return void
     * @throws Exception
     */
    public static function systemBoot(BootContext $context) : void;
}