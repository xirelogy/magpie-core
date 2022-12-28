<?php

namespace Magpie\Commands\Systems;

use Exception;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Consoles\Texts\StructuredText;
use Magpie\Facades\Console;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Simples\SimpleJSON;

/**
 * Refresh and maintain information about installed packages
 */
#[CommandSignature('sys:refresh-packages')]
#[CommandDescription('Refresh and maintain information about installed packages')]
class RefreshPackagesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        _used($request);

        $installedJsonPath = project_path('/vendor/composer/installed.json');

        $classes = iter_flatten(static::getClasses($installedJsonPath), false);

        $content = '<?php return ' . var_export($classes, true) . ';';

        $fs = LocalRootFileSystem::instance();

        $outputPath = project_path('/boot/cache/discovered_classes.php');
        $fs->createDirectory(dirname($outputPath));
        $fs->writeFile($outputPath, $content);

        Console::info('Package discovery completed');
    }


    /**
     * Get all discovered classes
     * @param string $installedJsonPath
     * @return iterable<string>
     * @throws Exception
     */
    protected static function getClasses(string $installedJsonPath) : iterable
    {
        $fs = LocalRootFileSystem::instance();

        if (!$fs->isFileExist($installedJsonPath)) return;

        $installed = SimpleJSON::decode($fs->readFile($installedJsonPath)->getData());
        if (!isset($installed->packages)) return;

        foreach ($installed->packages as $package) {
            yield from static::getPackageClasses($package);
        }
    }


    /**
     * Get package classes
     * @param object $package
     * @return iterable<string>
     */
    protected static function getPackageClasses(object $package) : iterable
    {
        if (!isset($package->name)) return;
        if (!isset($package->extra)) return;
        if (!isset($package->extra->magpie)) return;
        if (!isset($package->extra->magpie->classes)) return;

        $classes = $package->extra->magpie->classes;
        if (!is_array($classes)) return;

        Console::output(StructuredText::compound(
            'Discovered package ',
            StructuredText::info($package->name),
        ));

        yield from $classes;
    }
}