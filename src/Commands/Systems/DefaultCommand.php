<?php

namespace Magpie\Commands\Systems;

use Magpie\Commands\Command;
use Magpie\Commands\CommandRegistry;
use Magpie\Commands\Request;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\Facades\Console;
use Magpie\System\Kernel\Kernel;

/**
 * Default command to be run when nothing is specified
 */
class DefaultCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        Console::info(_format(_l('Magpie Framework version {{0}}'), Kernel::current()->getVersion()));
        Console::output('');

        Console::output(StructuredText::notice(_l('Usage:')));
        Console::output('  command [options] [arguments]');
        Console::output('');

        // Summarize all the commands
        $maxCommandLength = 0;
        $commandNamespaces = [
            '' => [],
        ];
        foreach (CommandRegistry::_all() as $command => $signature) {
            $namespace = static::getCommandNamespace($command);
            $maxCommandLength = max($maxCommandLength, strlen($command));

            $commandRows = $commandNamespaces[$namespace] ?? [];
            $commandRows[] = [$command, $signature->description];
            $commandNamespaces[$namespace] = $commandRows;
        }

        Console::output(StructuredText::notice(_l('Available commands:')));
        foreach ($commandNamespaces as $commandNamespace => $commandRows) {
            if (!is_empty_string($commandNamespace)) {
                Console::output(StructuredText::compound(' ', StructuredText::warning($commandNamespace)));
            }

            foreach ($commandRows as $commandRow) {
                [$command, $desc] = $commandRow;
                $padLength = $maxCommandLength - strlen($command);

                Console::output(StructuredText::compound(
                    '  ', StructuredText::info($command),
                    str_repeat(' ', $padLength), '  ',
                    ($desc ?? '-'),
                ));
            }
        }
    }


    /**
     * Detect and obtain command namespace
     * @param string $command
     * @return string
     */
    protected static function getCommandNamespace(string $command) : string
    {
        $colonPos = strpos($command, ':');
        if ($colonPos === false) return '';
        return substr($command, 0, $colonPos);
    }
}