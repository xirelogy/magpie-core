<?php

namespace Magpie\Routes\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Consoles\ConsoleTable;
use Magpie\Facades\Console;
use Magpie\Routes\RouteRegistry;

#[CommandSignature('route:list')]
#[CommandDescriptionL('List routes')]
class ListCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $table = new ConsoleTable([
            _l('Domain'),
            _l('Path'),
            _l('Method'),
            _l('Target'),
        ]);

        foreach (RouteRegistry::_all() as $route) {
            $table->addRow([
                $route->domain ?? '',
                $route->path,
                $route->method,
                $route->target,
            ]);
        }

        Console::display($table);
    }
}