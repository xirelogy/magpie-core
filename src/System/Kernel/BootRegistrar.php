<?php

namespace Magpie\System\Kernel;

/**
 * Definitions for boot registration
 */
abstract class BootRegistrar
{
    /**
     * @var string The class that the boot registrar is meant for
     */
    public readonly string $className;
    /**
     * @var array Boot extra provides
     */
    protected array $provides = [];
    /**
     * @var array<string> Boot dependencies
     */
    protected array $dependencies = [];


    /**
     * Constructor
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }


    /**
     * Declare a class to be included
     * @param string $class
     * @return $this
     */
    public function includes(string $class) : static
    {
        $this->onInclude($class);
        return $this;
    }


    /**
     * Handle class to be included
     * @param string $class
     * @return void
     */
    protected abstract function onInclude(string $class) : void;


    /**
     * Declare dependency
     * @param string $dependency
     * @return $this
     */
    public function depends(string $dependency) : static
    {
        $this->dependencies[] = $dependency;
        return $this;
    }


    /**
     * Declare (extra) provided
     * @param string $provide
     * @return $this
     */
    public function provides(string $provide) : static
    {
        $this->provides[] = $provide;
        return $this;
    }
}