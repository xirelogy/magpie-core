<?php

namespace Magpie\System\RunContexts;

use Exception;
use Magpie\Commands\Command;
use Magpie\Commands\CommandRegistry;
use Magpie\Commands\Impls\ImplRequest;
use Magpie\Commands\Systems\DefaultCommand;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Console;
use Magpie\Locales\I18n;
use Magpie\System\Kernel\Kernel;

/**
 * Context of execution to handle console request
 */
class ConsoleRunContext extends RunContext
{
    /**
     * Index from arguments (argv) to extract command from
     */
    protected const COMMAND_INDEX = 1;
    /**
     * @var int Last exit code
     */
    protected static int $lastExitCode = 0;


    /**
     * Return the last exit code
     * @return int
     */
    public static function getLastExitCode() : int
    {
        return static::$lastExitCode;
    }


    /**
     * @inheritDoc
     */
    public function run() : void
    {
        // Handle the arguments
        $argc = $_SERVER['argc'];
        $argv = $_SERVER['argv'];

        static::$lastExitCode = $this->runProtected($argc, $argv);
    }


    /**
     * Run protected
     * @param int $argc
     * @param array<string> $argv
     * @return int
     */
    protected function runProtected(int $argc, array $argv) : int
    {
        try {
            if ($argc < 2) {
                return $this->runWithoutCommand($argc, $argv);
            } else {
                return $this->runWithCommand($argc, $argv);
            }
        } catch (Exception $ex) {
            Console::error($ex->getMessage());
            return 1;
        }
    }


    /**
     * Run without command
     * @param int $argc
     * @param array<string> $argv
     * @return int
     * @throws Exception
     */
    protected function runWithoutCommand(int $argc, array $argv) : int
    {
        _used($argc, $argv);

        $request = new ImplRequest('', [], []);

        $commandInstance = new DefaultCommand();
        return $commandInstance->run($request);
    }


    /**
     * Run with command
     * @param int $argc
     * @param array<string> $argv
     * @return int
     * @throws Exception
     */
    protected function runWithCommand(int $argc, array $argv) : int
    {
        $command = $argv[static::COMMAND_INDEX] ?? throw new UnexpectedException();

        $handler = CommandRegistry::_route($command);
        $request = $handler->createRequest($argc, $argv, static::COMMAND_INDEX);

        $commandClass = $handler->payloadClassName;
        if (!is_subclass_of($commandClass, Command::class)) throw new ClassNotOfTypeException($commandClass, Command::class);

        /** @var Command $commandInstance */
        $commandInstance = new $commandClass;

        return $commandInstance->run($request);
    }


    /**
     * @inheritDoc
     */
    protected static function onCapture() : static
    {
        // Prepare the registry
        CommandRegistry::_boot();

        // Register the console
        $kernel = Kernel::current();
        $kernel->registerProvider(Consolable::class, $kernel->getConfig()->createDefaultConsolable());

        // Handle LANG environment variable
        $lang = $_SERVER['LANG'] ?? null;
        if ($lang !== null) I18n::setCurrentLocale($lang);

        return new static();
    }
}