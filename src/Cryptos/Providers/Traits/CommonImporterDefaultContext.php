<?php

namespace Magpie\Cryptos\Providers\Traits;

use Magpie\Cryptos\Context;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Common implementation for importer's default context
 */
trait CommonImporterDefaultContext
{
    /**
     * @var Context|null A specific default context to be use
     */
    protected static ?Context $defaultContext = null;


    /**
     * Get default context for importing this kind of cryptographic object
     * @return Context
     * @throws SafetyCommonException
     */
    public static function getDefaultContext() : Context
    {
        return static::$defaultContext ?? Context::getDefault();
    }


    /**
     * Register a default context for importing this kind of cryptographic object
     * @param Context $context
     * @return void
     */
    public static function registerDefaultContext(Context $context) : void
    {
        static::$defaultContext = $context;
    }
}