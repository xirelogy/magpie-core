<?php

namespace Magpie\Schedules;

use Magpie\Commands\Command;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Schedules\Impls\CommandScheduleRunner;

/**
 * Command schedule definition
 */
class CommandScheduleDefinition extends ScheduleDefinition
{
    /**
     * Constructor
     * @param class-string<Command>|string $commandSpec
     * @throws SafetyCommonException
     */
    protected function __construct(string $commandSpec)
    {
        $runner = new CommandScheduleRunner($commandSpec);

        parent::__construct($runner);
    }


    /**
     * Specify command arguments
     * @param string ...$commandArguments
     * @return $this
     */
    public function withArguments(string ...$commandArguments) : static
    {
        $this->runner->withArguments(...$commandArguments);
        return $this;
    }


    /**
     * Create an instance of definition from command specification
     * @param class-string<Command>|string $commandSpec
     * @return static
     * @throws SafetyCommonException
     */
    public static function createFrom(string $commandSpec) : static
    {
        return new static($commandSpec);
    }
}