<?php

namespace Magpie\System\Impls;

use Exception;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Actionable boot registrar
 * @internal
 */
class BootActionableRegistrar extends BootRegistrar
{
    /**
     * @var bool If boot-up completed
     */
    protected bool $isBooted = false;
    /**
     * @var array<static> All dependant registrars
     */
    protected array $subRegistrars = [];


    /**
     * Constructor
     * @param string $className
     */
    protected function __construct(string $className)
    {
        parent::__construct($className);
    }


    /**
     * @inheritDoc
     */
    protected function onInclude(string $class) : void
    {
        $subRegistrar = static::fromRegistration($class);
        if ($subRegistrar !== null) $this->subRegistrars[] = $subRegistrar;
    }


    /**
     * If boot up completed
     * @return bool
     */
    public function isBooted() : bool
    {
        return $this->isBooted;
    }


    /**
     * Run boot up
     * @param BootContext $context
     * @return bool
     * @throws Exception
     */
    public function runBoot(BootContext $context) : bool
    {
        // Check all dependencies
        foreach ($this->dependencies as $dependency) {
            if (!$context->isBooted($dependency)) return false;
        }

        $className = $this->className;
        if (!is_subclass_of($className, SystemBootable::class)) throw new ClassNotOfTypeException($className, SystemBootable::class);

        $className::systemBoot($context);
        return true;
    }


    /**
     * Get all provided
     * @return iterable<string>
     */
    public function getBootProvides() : iterable
    {
        yield $this->className;

        foreach ($this->provides as $provide) {
            yield $provide;
        }
    }


    /**
     * All sub registrars
     * @return iterable<static>
     */
    public function getSubRegistrars() : iterable
    {
        foreach ($this->subRegistrars as $subRegistrar) {
            yield $subRegistrar;
            yield from $subRegistrar->getSubRegistrars();
        }
    }


    /**
     * Create an actionable registrar from given registration
     * @param string $class
     * @return static|null
     */
    public static function fromRegistration(string $class) : ?static
    {
        if (!is_subclass_of($class, SystemBootable::class)) return null;

        $ret = new static($class);
        if (!$class::systemBootRegister($ret)) return null;

        return $ret;
    }
}