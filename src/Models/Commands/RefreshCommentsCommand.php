<?php

namespace Magpie\Models\Commands;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Exceptions\ParseFailedException;
use Magpie\Facades\Console;
use Magpie\Facades\FileSystem\Providers\Local\LocalFileSystem;
use Magpie\Logs\Formats\CleanConsoleLogStringFormat;
use Magpie\Models\Commands\Features\DatabaseCommandFeature;

/**
 * Refresh model source files comments
 */
#[CommandSignature('db:refresh-comments {--path=}')]
#[CommandDescriptionL('Refresh model source files comments')]
#[CommandOptionDescriptionL('path', 'Specific path to target source files')]
class RefreshCommentsCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $path = $request->options->optional('path', static::createPathParser());
        $paths = $path !== null ? [ $path ] : null;

        $listener = DatabaseCommandFeature::createRefreshCommentsListener(Console::asLogger(new CleanConsoleLogStringFormat()));
        DatabaseCommandFeature::refreshComments($listener, $paths);
    }


    /**
     * Create parser for parsing path
     * @return Parser
     */
    protected static function createPathParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $fs = LocalFileSystem::initializeFromWorkDir();
            if (!$fs->isDirectoryExist($value)) throw new ParseFailedException(_l('directory does not exist'));
            return $fs->getRealPath($value);
        });
    }
}