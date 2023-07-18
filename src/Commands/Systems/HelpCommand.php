<?php

namespace Magpie\Commands\Systems;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandArgumentDescriptionL;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\CommandRegistry;
use Magpie\Commands\Request;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\Facades\Console;
use Magpie\General\Sugars\Quote;
use Magpie\Locales\Concepts\Localizable;

/**
 * Show help for given command
 */
#[CommandSignature('help {command?}')]
#[CommandDescriptionL('Show help for given command')]
#[CommandArgumentDescriptionL('command', 'The command to show help for')]
class HelpCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $command = $request->arguments->optional('command', StringParser::createTrimEmptyAsNull());
        $command = $command ?? 'help';

        $signature = CommandRegistry::_route($command);

        Console::output(StructuredText::strong(_l('Description:')));
        Console::output('  ' . $signature->description);
        Console::output('');

        $outSignatures = [
            '  ',
            StructuredText::warning($signature->command),
            ' ',
            StructuredText::note('[options]'),
        ];

        $maxPrefixLength = 0;

        $outArguments = [];
        $hasArgument = false;
        foreach ($signature->arguments as $argument) {
            $outSignatures[] = ' ';

            $argumentName = $argument->name;
            if (!$argument->isMandatory) $argumentName .= '?';
            $outSignatures[] = Quote::brace($argumentName);

            static::addArgOptDescription($maxPrefixLength, $outArguments, $argumentName, $argument->description);
            $hasArgument = true;
        }

        $outOptions = [];
        $hasOptions = false;
        foreach ($signature->options as $option) {
            $optionName = '--' . $option->name;
            if ($option->hasPayload) $optionName .= '=';

            static::addArgOptDescription($maxPrefixLength, $outOptions, $optionName, $option->description);
            $hasOptions = true;
        }

        Console::output(StructuredText::strong(_l('Usage:')));
        Console::output(StructuredText::compound(...$outSignatures));
        Console::output('');

        if ($hasArgument) {
            Console::output(StructuredText::strong(_l('Arguments:')));
            foreach ($outArguments as $argument => $desc) {
                $spaceLength = $maxPrefixLength - strlen($argument);

                Console::output(StructuredText::compound(
                    '  ',
                    StructuredText::warning($argument),
                    str_repeat(' ', $spaceLength + 2),
                    $desc,
                ));
            }
            Console::output('');
        }

        if ($hasOptions) {
            Console::output(StructuredText::strong(_l('Options:')));
            foreach ($outOptions as $option => $desc) {
                $spaceLength = $maxPrefixLength - strlen($option);

                Console::output(StructuredText::compound(
                    '  ',
                    StructuredText::warning($option),
                    str_repeat(' ', $spaceLength + 2),
                    $desc,
                ));
            }
            Console::output('');
        }
    }


    /**
     * Add argument/option description
     * @param int $maxPrefixLength
     * @param array $outMap
     * @param string $prefix
     * @param string|Localizable|null $desc
     * @return void
     */
    protected static function addArgOptDescription(int &$maxPrefixLength, array &$outMap, string $prefix, string|Localizable|null $desc) : void
    {
        $prefixLength = strlen($prefix);
        $maxPrefixLength = max($maxPrefixLength, $prefixLength);

        $desc = $desc ?? '-';
        $outMap[$prefix] = $desc;
    }
}