<?php

namespace Magpie\System\Kernel;

use Magpie\Commands\CommandRegistry;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\SystemBootable;

/**
 * Representing of core features
 */
class CoreFeatures implements SystemBootable
{
    use StaticClass;


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        CommandRegistry::includeDirectory(__DIR__ . '/../../Commands/Systems');
        CommandRegistry::includeDirectory(__DIR__ . '/../../Routes/Commands');

        ClassFactory::includeDirectory(__DIR__ . '/../../Logs/Relays');
    }
}