<?php

namespace Magpie\System\RunContexts;

use Exception;
use Magpie\Codecs\Parsers\ArrayParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Command;
use Magpie\Commands\CommandRegistry;
use Magpie\Commands\Impls\ImplRequest;
use Magpie\Commands\Systems\DefaultCommand;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Console;
use Magpie\HttpServer\ServerCollection;
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
     * @var ServerCollection Server variables
     */
    protected readonly ServerCollection $serverVars;


    /**
     * Constructor
     * @param ServerCollection $serverVars
     */
    protected function __construct(ServerCollection $serverVars)
    {
        parent::__construct();

        $this->serverVars = $serverVars;
    }


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
        static::$lastExitCode = $this->runProtected();
    }


    /**
     * Run protected
     * @return int
     */
    protected function runProtected() : int
    {
        try {
            /** @var int $argc */
            $argc = $this->serverVars->requires('argc', IntegerParser::create()->withMin(0));
            /** @var array<string> $argv */
            $argv = $this->serverVars->requires('argv', ArrayParser::create()->withChain(StringParser::create()));

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

        $request = new ImplRequest('', [], [], $this->serverVars);

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
        $request = $handler->createRequest($this->serverVars, $argc, $argv, static::COMMAND_INDEX);

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

        // Capture server variables
        $serverVars = ServerCollection::capture();

        // Register the console
        $kernel = Kernel::current();
        $kernel->registerProvider(Consolable::class, $kernel->getConfig()->createDefaultConsolable());

        // Handle LANG environment variable
        $lang = $serverVars->optional('LANG', StringParser::createTrimEmptyAsNull());
        if ($lang !== null) I18n::setCurrentLocale($lang);

        return new static($serverVars);
    }
}