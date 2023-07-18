<?php

namespace Magpie\Commands\Systems;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Exceptions\MustBeValuesParseFailedException;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\System\Kernel\Kernel;

/**
 * Check or change system maintenance status
 */
#[CommandSignature('sys:maintenance {set?}')]
#[CommandDescriptionL('Check system maintenance state or change system maintenance state')]
class MaintenanceCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        /** @var bool|null $isSet */
        $isSet = $request->arguments->optional('set', static::createSetParser());

        $maintainer = Kernel::current()->getSystemMaintainer();

        if ($isSet !== null) {
            $maintainer->setMaintenanceMode(!$isSet);
        }

        if ($maintainer->isUnderMaintenance()) {
            Console::info(_l('System is currently under maintenance'));
        } else {
            Console::info(_l('System is currently available'));
        }
    }


    /**
     * Create a parser to parse the set state
     * @return Parser<bool>
     */
    protected static function createSetParser() : Parser
    {
        return ClosureParser::create(function(mixed $value, ?string $hintName) : bool {
            $value = StringParser::create()->parse($value, $hintName);
            return match ($value) {
                'up' => true,
                'down' => false,
                default => throw new MustBeValuesParseFailedException(['up', 'down']),
            };
        });
    }
}