<?php

namespace Magpie\General\Traits;

/**
 * Current class is accessed as a singleton instance
 */
trait SingletonInstance
{
    /**
     * @var static|null Current instance
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    protected static ?self $instance = null;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Get current instance
     * @return static
     */
    public final static function instance() : static
    {
        if (static::$instance === null) {
            static::$instance = static::createInstance();
        }

        return static::$instance;
    }


    /**
     * Create an instance
     * @return static
     */
    protected static function createInstance() : static
    {
        return new static();
    }
}