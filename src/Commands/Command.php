<?php

namespace Magpie\Commands;

use Exception;
use Magpie\Commands\Concepts\CommandRunnable;
use Magpie\Commands\Impls\ConsoleExceptionHandler;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Contexts\ScopedCollection;

/**
 * A console command
 */
abstract class Command implements CommandRunnable
{
    /**
     * Exit code for success
     */
    public const EXIT_SUCCESS = 0;
    /**
     * Exit code for failure (default)
     */
    public const EXIT_FAILURE = 255;

    /**
     * @var int Exit code
     */
    protected int $exitCode = self::EXIT_SUCCESS;


    /**
     * @inheritDoc
     */
    public function run(Request $request) : int
    {
        // Reset the exit code
        $this->exitCode = static::EXIT_SUCCESS;

        // Setup scope
        $scoped = new ScopedCollection($this->getScopedItems());

        try {
            $this->onRun($request);
            $scoped->succeeded();
            return $this->exitCode;
        } catch (Exception $ex) {
            $scoped->crash($ex);
            return ConsoleExceptionHandler::handle($ex);
        } finally {
            $scoped->release();
        }
    }


    /**
     * Run the command
     * @param Request $request
     * @throws Exception
     */
    protected abstract function onRun(Request $request) : void;


    /**
     * All scoped items
     * @return iterable<Scoped>
     */
    protected function getScopedItems() : iterable
    {
        return [];
    }


    /**
     * The command
     * @return string|null
     */
    public static final function getCommand() : ?string
    {
        $signature = CommandRegistry::_getSignature(static::class);
        return $signature?->command ?? null;
    }
}