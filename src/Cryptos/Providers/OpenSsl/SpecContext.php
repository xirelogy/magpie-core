<?php

namespace Magpie\Cryptos\Providers\OpenSsl;

use Magpie\Cryptos\Context;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * X.509 certificate supported by OpenSSL
 */
#[FactoryTypeClass(SpecContext::TYPECLASS, Context::class)]
class SpecContext extends Context
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'openssl';



    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(Context::class)
            ;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        parent::systemBoot($context);

        ClassFactory::includeDirectory(__DIR__);
        ClassFactory::includeDirectory(__DIR__ . '/Impls');

        ClassFactory::setDefaultTypeClassCheck(Context::class, function () : ?string {
            if (!extension_loaded('openssl')) return null;
            return static::TYPECLASS;
        });
    }
}