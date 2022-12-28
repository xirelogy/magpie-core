<?php

namespace Magpie\Schedules\Impls;

use Magpie\Commands\Command;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Sugars\Quote;
use Magpie\System\Process\ProcessCommandLine;

/**
 * May run a command in scheduling
 */
#[FactoryTypeClass(CommandScheduleRunner::TYPECLASS, ScheduleRunner::class)]
class CommandScheduleRunner extends ScheduleRunner
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'command';

    /**
     * @var string Command to be run
     */
    protected readonly string $command;
    /**
     * @var array<string> Command arguments
     */
    protected array $commandArguments = [];


    /**
     * Constructor
     * @param class-string<Command>|string $commandSpec
     * @throws SafetyCommonException
     */
    public function __construct(string $commandSpec)
    {
        $this->command = static::acceptCommandSpec($commandSpec);
    }


    /**
     * Specify command arguments
     * @param string ...$commandArguments
     * @return $this
     */
    public function withArguments(string ...$commandArguments) : static
    {
        foreach ($commandArguments as $commandArgument) {
            $this->commandArguments[] = $commandArgument;
        }

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getDesc() : string
    {
        return Quote::angle('cmd') . ' ' . $this->command . ' ' . Quote::square(implode(', ', $this->commandArguments));
    }


    /**
     * @inheritDoc
     */
    protected function onCreateProcessCommandLine() : ProcessCommandLine
    {
        return ProcessCommandLine::fromCommand($this->command, ...$this->commandArguments);
    }


    /**
     * @inheritDoc
     */
    protected function onSourceCacheExport(array &$ret) : void
    {
        $ret['command'] = $this->command;
        $ret['commandArguments'] = $this->commandArguments;
    }


    /**
     * @inheritDoc
     */
    protected static function onSourceCacheImport(array $data) : static
    {
        $ret = new static($data['command']);
        $ret->commandArguments = $data['commandArguments'];
        return $ret;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Accept command specification
     * @param class-string<Command>|string $commandSpec
     * @return string
     * @throws SafetyCommonException
     */
    protected static function acceptCommandSpec(string $commandSpec) : string
    {
        if (is_subclass_of($commandSpec, Command::class)) {
            return $commandSpec::getCommand() ?? throw new NullException();
        }

        return $commandSpec;
    }
}

