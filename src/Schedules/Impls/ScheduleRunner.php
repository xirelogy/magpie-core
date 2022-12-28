<?php

namespace Magpie\Schedules\Impls;

use Exception;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Schedules\Concepts\ScheduleRunnable;
use Magpie\System\Concepts\SourceCacheTranslatable;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;

/**
 * Runner in scheduling
 */
abstract class ScheduleRunner implements ScheduleRunnable, SourceCacheTranslatable
{
    /**
     * @inheritDoc
     */
    public function _createProcess() : Process
    {
        $commandLine = $this->onCreateProcessCommandLine();
        return Process::fromCommandLine($commandLine);
    }


    /**
     * Create process command line
     * @return ProcessCommandLine
     * @throws SafetyCommonException
     */
    protected abstract function onCreateProcessCommandLine() : ProcessCommandLine;




    /**
     * @inheritDoc
     */
    public final function sourceCacheExport() : array
    {
        $ret = [
            'typeClass' => static::getTypeClass(),
        ];

        $this->onSourceCacheExport($ret);

        return $ret;
    }


    /**
     * Specifically export for source cache
     * @param array $ret
     * @return void
     */
    protected abstract function onSourceCacheExport(array &$ret) : void;


    /**
     * @inheritDoc
     */
    public static final function sourceCacheImport(array $data) : static
    {
        $typeClass = $data['typeClass'];
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::onSourceCacheImport($data);
    }


    /**
     * Specifically import from source cache
     * @param array $data
     * @return static
     * @throws Exception
     */
    protected static abstract function onSourceCacheImport(array $data) : static;
}