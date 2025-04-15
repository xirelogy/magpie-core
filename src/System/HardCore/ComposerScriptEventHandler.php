<?php

namespace Magpie\System\HardCore;

use Composer\Script\Event;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;

/**
 * Handle events from composer's script
 */
final class ComposerScriptEventHandler
{
    use StaticClass;


    /**
     * Handle 'post-autoload-dump'
     * @param Event $event
     * @return void
     */
    public static function onPostAutoloadDump(Event $event) : void
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';

        static::clearPackagesCache();
    }


    /**
     * Clear all packages cache
     * @return void
     */
    protected static function clearPackagesCache() : void
    {
        $dir = getcwd();
        if ($dir === false) return;

        // Delete the files
        Excepts::noThrow(fn() => LocalRootFileSystem::instance()->deleteFile($dir . '/boot/cache/discovered_classes.php'));
    }
}